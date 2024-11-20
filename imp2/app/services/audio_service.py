import os
import torch
import numpy as np
from app.models.audio.voice_filter import VoiceFilter, filter_audio

# Define and load configurations and models
def initialize_audio_service():
    config = {
        "audio": {"num_freq": 257},
        "model": {
            "emb_dim": 256,
            "lstm_dim": 512,
            "fc1_dim": 256,
            "fc2_dim": 1
        }
    }

    # Load models
    speaker_embedding = torch.randn(1, config['model']['emb_dim'])  # Placeholder speaker embedding
    voice_filter = VoiceFilter(config)
    silero_model = torch.jit.load("app/models/audio/silero-vad/src/silero_vad/data/silero_vad.jit")
    return config, voice_filter, silero_model, speaker_embedding

# Load the Silero VAD model
def load_silero_model():
    model_path = "app/models/audio/silero-vad/src/silero_vad/data/silero_vad.jit"
    if os.path.exists(model_path):
        silero_model = torch.jit.load(model_path)
        print("Silero VAD model loaded successfully.")
        return silero_model
    else:
        print("Model file not found.")
        return None

# Process the audio file with VoiceFilter and Silero VAD
def process_audio_with_vad(file_path, silero_model, voice_filter, speaker_embedding, config):
    # Filter the audio with VoiceFilter
    filtered_audio, sr = filter_audio(file_path, voice_filter, speaker_embedding, config)
    
    # Normalize filtered audio
    filtered_audio = filtered_audio / torch.max(torch.abs(filtered_audio)) if torch.max(torch.abs(filtered_audio)) > 0 else filtered_audio

    segment_length = 512
    overlap = segment_length // 6
    audio_tensor = filtered_audio.unsqueeze(0)  # [1, T]

    speech_probs_list = []
    for start in range(0, audio_tensor.shape[-1] - overlap, segment_length - overlap):
        chunk = audio_tensor[:, start:start + segment_length]
        if chunk.shape[-1] != segment_length:
            continue
        with torch.no_grad():
            speech_prob = silero_model(chunk, sr)
            if speech_prob is not None and speech_prob.numel() > 0:
                speech_probs_list.append(speech_prob.squeeze().numpy())

    vad_labels = np.concatenate([np.atleast_1d(sp) for sp in speech_probs_list]) > 0.3 if speech_probs_list else np.array([])
    return vad_labels

# Determine if audio is normal or abnormal based on speech detection
def detect_anomaly_in_audio(file_path, silero_model, voice_filter, speaker_embedding, config):
    vad_labels = process_audio_with_vad(file_path, silero_model, voice_filter, speaker_embedding, config)
    
    # If speech is detected, label as abnormal, otherwise normal
    if np.sum(vad_labels) > 0:
        return {
            'status': 'success',
            'label': "abnormal",
            'speech_detected_count': np.sum(vad_labels).item()
        }
    else:
        return {
            'status': 'success',
            'label': "normal",
            'speech_detected_count': 0
        }
