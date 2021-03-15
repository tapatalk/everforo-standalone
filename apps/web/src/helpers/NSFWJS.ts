// import * as tf from '@tensorflow/tfjs';
// // import * as SuperGif from 'libgif';

// export const NSFW_CLASSES: { [classId: number]: string } = {
//     0: 'Drawing',
//     1: 'Hentai',
//     2: 'Neutral',
//     3: 'Porn',
//     4: 'Sexy',
// };

// interface frameResult {
//     index: number;
//     totalFrames: number;
//     predictions: Array<Object>;
// }

// interface classifyConfig {
//     topk?: number;
//     onFrame?: (result: frameResult) => {};
//     // setGifControl?: (gifControl: typeof SuperGif) => {};
// }

// interface nsfwjsOptions {
//     size: number;
// }

// const BASE_PATH = 'https://groups.tapatalk-cdn.com/static/nsfw/min_nsfwjs/';
// const IMAGE_SIZE = 299; // default to inception v3 v2
// const INDEXEDDB_PATH = 'indexeddb://nsfwjs-model';

// export async function loadModel(base = BASE_PATH, options = {size: IMAGE_SIZE})
//     : Promise<NSFWJS> {
//     if (!tf) {
//         throw new Error(
//             `Cannot find TensorFlow.js. If you are using a <script> tag, please ` +
//             `also include @tensorflow/tfjs on the page before using this model.`
//         );
//     }

//     const nsfwnet = new NSFWJS(base, options);
//     await nsfwnet.load();
//     return nsfwnet;
// }

// interface IOHandler {
//     load: () => any
// }

// export class NSFWJS {
//     public endpoints!: string[];

//     private options: nsfwjsOptions;
//     private pathOrIOHandler: string | IOHandler;
//     private model!: tf.LayersModel;
//     private intermediateModels: { [layerName: string]: tf.LayersModel | undefined } = {};

//     private normalizationOffset: tf.Scalar;

//     constructor(
//         modelPathBaseOrIOHandler: string | IOHandler,
//         options: nsfwjsOptions
//     ) {
//         this.options = options;
//         this.normalizationOffset = tf.scalar(255);

//         if (typeof modelPathBaseOrIOHandler === 'string') {
//             this.pathOrIOHandler = `${modelPathBaseOrIOHandler}model.json`;
//         } else {
//             this.pathOrIOHandler = modelPathBaseOrIOHandler;
//         }
//     }

//     async load() {
//         // this is a Layers Model
//         // load from s3 bucket and save it to local indexeddb
//         try {
//             this.model = await tf.loadLayersModel(INDEXEDDB_PATH);
//         } catch (e) {
//             this.model = await tf.loadLayersModel(this.pathOrIOHandler);
//             await this.model.save(INDEXEDDB_PATH);
//         }

//         this.endpoints = this.model.layers.map(l => l.name);

//         const {size} = this.options;

//         // Warmup the model.
//         const result = tf.tidy(() =>
//             this.model.predict(tf.zeros([1, size, size, 3]) as tf.Tensor)
//         ) as tf.Tensor;
//         await result.data();
//         result.dispose();
//     }

//     /**
//      * Infers through the model. Optionally takes an endpoint to return an
//      * intermediate activation.
//      *
//      * @param img The image to classify. Can be a tensor or a DOM element image,
//      * video, or canvas.
//      * @param endpoint The endpoint to infer through. If not defined, returns
//      * logits.
//      */
//     infer(
//         img:
//             | tf.Tensor3D
//             | ImageData
//             | HTMLImageElement
//             | HTMLCanvasElement
//             | HTMLVideoElement,
//         endpoint?: string
//     ): tf.Tensor {
//         if (endpoint != null && this.endpoints.indexOf(endpoint) === -1) {
//             throw new Error(
//                 `Unknown endpoint ${endpoint}. Available endpoints: ` +
//                 `${this.endpoints}.`
//             );
//         }

//         return tf.tidy(() => {
//             if (!(img instanceof tf.Tensor)) {
//                 img = tf.browser.fromPixels(img as HTMLImageElement)
//             }

//             // Normalize the image from [0, 255] to [0, 1].
//             const normalized = img
//                 .toFloat()
//                 .div(this.normalizationOffset) as tf.Tensor3D;

//             // Resize the image to
//             let resized = normalized;
//             const {size} = this.options;
//             // check width and height if resize needed
//             if (img.shape[0] !== size || img.shape[1] !== size) {
//                 const alignCorners = true;
//                 resized = tf.image.resizeBilinear(
//                     normalized as any,
//                     [size, size],
//                     alignCorners
//                 ) as any;
//             }

//             // Reshape to a single-element batch so we can pass it to predict.
//             const batched = resized.reshape([1, size, size, 3]);

//             let model: tf.LayersModel;
//             if (endpoint == null) {
//                 model = this.model;
//             } else {
//                 if (this.intermediateModels[endpoint] === undefined) {
//                     const layer = this.model.layers.find(l => l.name === endpoint) as tf.LayersModel;
//                     this.intermediateModels[endpoint] = tf.model({
//                         inputs: this.model.inputs,
//                         outputs: layer.output
//                     });
//                 }
//                 model = this.intermediateModels[endpoint] as tf.LayersModel;
//             }

//             // return logits
//             return model.predict(batched as any) as tf.Tensor2D;
//         }) as tf.Tensor;
//     }

//     /**
//      * Classifies an image from the 5 classes returning a map of
//      * the most likely class names to their probability.
//      *
//      * @param img The image to classify. Can be a tensor or a DOM element image,
//      * video, or canvas.
//      * @param topk How many top values to use. Defaults to 5
//      */
//     async classify(
//         img:
//             | tf.Tensor3D
//             | ImageData
//             | HTMLImageElement
//             | HTMLCanvasElement
//             | HTMLVideoElement,
//         topk = 5
//     ): Promise<Array<{ className: string; probability: number }>> {
//         const logits = this.infer(img) as tf.Tensor2D;

//         const classes = await getTopKClasses(logits as any, topk);

//         logits.dispose();

//         return classes;
//     }

//     /**
//      * Classifies a gif from the 5 classes returning a map of
//      * the most likely class names to their probability.
//      *
//      * @param gif The gif to classify. DOM element image
//      * @param config param configuration for run
//      */
//     // async classifyGif(
//     //     gif: HTMLImageElement,
//     //     config: classifyConfig = { topk: 5 }
//     // ): Promise<Array<Array<{ className: string; probability: number }>>> {
//     //     let gifObj = new SuperGif({ gif })
//     //     return new Promise(resolve => {
//     //         gifObj.load(async () => {
//     //             const arrayOfClasses = []
//     //             const gifLength = gifObj.get_length()
//     //             for (let i = 1; i <= gifLength; i++) {
//     //                 gifObj.move_to(i)
//     //                 const classes = await this.classify(gifObj.get_canvas(), config.topk)
//     //                 // Update to onFrame
//     //                 if (config.onFrame)
//     //                     config.onFrame({
//     //                         index: i,
//     //                         totalFrames: gifLength,
//     //                         predictions: classes
//     //                     })
//     //                 // Store the goods
//     //                 arrayOfClasses.push(classes)
//     //             }
//     //             // save gifObj if needed
//     //             config.setGifControl && config.setGifControl(gifObj)
//     //             // try to clean up
//     //             gifObj = null
//     //             resolve(arrayOfClasses)
//     //         })
//     //     })
//     // }
// }

// async function getTopKClasses(
//     logits: tf.Tensor2D,
//     topK: number
// ): Promise<Array<{ className: string; probability: number }>> {
//     const values = await logits.data()

//     const valuesAndIndices = []
//     for (let i = 0; i < values.length; i++) {
//         valuesAndIndices.push({value: values[i], index: i})
//     }
//     valuesAndIndices.sort((a, b) => {
//         return b.value - a.value
//     })
//     const topkValues = new Float32Array(topK)
//     const topkIndices = new Int32Array(topK)
//     for (let i = 0; i < topK; i++) {
//         topkValues[i] = valuesAndIndices[i].value
//         topkIndices[i] = valuesAndIndices[i].index
//     }

//     const topClassesAndProbs = []
//     for (let i = 0; i < topkIndices.length; i++) {
//         topClassesAndProbs.push({
//             className: NSFW_CLASSES[topkIndices[i]],
//             probability: topkValues[i]
//         })
//     }
//     return topClassesAndProbs
// }