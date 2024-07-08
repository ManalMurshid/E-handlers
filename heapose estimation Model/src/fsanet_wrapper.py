import tensorflow as tf
import numpy as np
from termcolor import colored

tf.get_logger().setLevel('ERROR')

class FsanetWrapper:
    def __init__(self, graph="graph/fsanet.pb", memory_fraction=0.7):
        self.graph_fb = graph
        self.config = tf.compat.v1.ConfigProto()
        self.config.gpu_options.per_process_gpu_memory_fraction = memory_fraction
        self.config.gpu_options.allow_growth = True
        self.config.log_device_placement = False

        self.graph = tf.Graph()
        with self.graph.as_default():
            graph_def = tf.compat.v1.GraphDef()
            with tf.io.gfile.GFile(self.graph_fb, 'rb') as fid:
                graph_def.ParseFromString(fid.read())
                tf.import_graph_def(graph_def)
        
        self.output_tensor = self.graph.get_tensor_by_name('import/average_1/truediv:0')
        self.input_tensor = self.graph.get_tensor_by_name('import/input_27:0')

    def predict(self, images):
        if images.ndim == 3:
            inputs = np.expand_dims(images, axis=0)
        elif images.ndim < 3 or images.ndim > 4:
            raise Exception("check images dims, require [?, 64, 64, 3], images dims {}".format(images.ndim))
        else:
            inputs = images

        outputs = tf.compat.v1.Session(graph=self.graph, config=self.config).run(
            self.output_tensor,
            feed_dict={self.input_tensor: inputs}
        )

        return np.asarray(outputs)[0]
