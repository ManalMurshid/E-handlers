import torch
import torch.nn as nn
import torch.nn.functional as F
import librosa
import numpy as np

class VoiceFilter(nn.Module):
    def __init__(self, config):
        super(VoiceFilter, self).__init__()
        self.config = config
        self.audio = self.config['audio']

        convs = [
            nn.ZeroPad2d((3, 3, 0, 0)),
            nn.Conv2d(1, 64, kernel_size=(1, 7)),
            nn.BatchNorm2d(64), nn.ReLU(),
            nn.ZeroPad2d((0, 0, 3, 3)),
            nn.Conv2d(64, 64, kernel_size=(7, 1)),
            nn.BatchNorm2d(64), nn.ReLU(),
            nn.ZeroPad2d(2),
            nn.Conv2d(64, 64, kernel_size=(5, 5)),
            nn.BatchNorm2d(64), nn.ReLU(),
            nn.ZeroPad2d((2, 2, 4, 4)),
            nn.Conv2d(64, 64, kernel_size=(5, 5), dilation=(2, 1)),
            nn.BatchNorm2d(64), nn.ReLU(),
            nn.Conv2d(64, 8, kernel_size=(1, 1)), 
            nn.BatchNorm2d(8), nn.ReLU()
        ]
        self.conv = nn.Sequential(*convs)

        self.lstm = nn.LSTM(8 * self.audio['num_freq'] + config['model']['emb_dim'], config['model']['lstm_dim'], batch_first=True, bidirectional=True)
        self.fc1 = nn.Linear(2 * config['model']['lstm_dim'], config['model']['fc1_dim'])
        self.fc2 = nn.Linear(config['model']['fc1_dim'], config['model']['fc2_dim'])

    def forward(self, x, speaker_embedding):
        x = x.unsqueeze(1)  # [B, 1, T, num_freq]
        x = self.conv(x)
        x = x.transpose(1, 2).contiguous()  # [B, T, 8, num_freq]
        x = x.view(x.size(0), x.size(1), -1)  # [B, T, 8 * num_freq]

        speaker_embedding = speaker_embedding.unsqueeze(1).repeat(1, x.size(1), 1)  # [B, T, emb_dim]
        x = torch.cat((x, speaker_embedding), dim=2)
        x, _ = self.lstm(x)
        x = F.relu(x)
        x = self.fc1(x)
        x = F.relu(x)
        x = self.fc2(x)
        x = torch.sigmoid(x)
        return x

def filter_audio(input_path, model, speaker_embedding, config):
    y, sr = librosa.load(input_path, sr=16000)
    S = librosa.stft(y, n_fft=512, hop_length=256, win_length=512)
    spectrogram = torch.from_numpy(np.abs(S).T).unsqueeze(0)

    with torch.no_grad():
        output_mask = model(spectrogram, speaker_embedding)

    masked_spectrogram = spectrogram.squeeze(0) * output_mask.squeeze(0)
    masked_audio = librosa.istft(masked_spectrogram.numpy().T, hop_length=256, win_length=512)
    
    return torch.from_numpy(masked_audio).float(), sr
