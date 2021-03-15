<template>
    <div
        v-if="show"
        :class="['image-block', {'single': imageList.length == 1}]"
        ref="block"
        :style="blockStyle"
    >
        <div
            v-for="(image, index) in imageList"
            :class="['image-box']"
            :key="index"
            :style="image.style"
        >
            <img
                :src="image.src"
                :style="image.imgStyle"
                v-on:click="onViewImage(image.src)"
            >
            <div
                v-if="index === (imageList.length - 1) && plus"
                class="plus"
            >
                <span>+{{plus}}</span>
            </div>
        </div>
        <div
            v-if="viewImg"
            class="gallery"
            ref='gallery'
            v-on:click="closeGallery"
        >
            <div class="img-box">
                <img :src="viewImg" alt="">
            </div>
        </div>
    </div>
    <div
        v-else
        class="placeholder"
    >
    </div>
</template>
<script lang="ts">
    import {Component, Prop, Ref, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from "@/helpers/Utils";
    import {DeltaOpsInterface} from '@/helpers/Interfaces';
    import {documentWidth, swapArrayItem, thumbToOrigin, windowWidth} from '@/helpers/Utils';

    type imageAttr = { src: string, width: number, height: number, style?: Record<string, string>, imgStyle?: Record<string, string> }[];

    @Component
    export default class ImageBlock extends Vue {
        @Ref('block')
        readonly block!: HTMLDivElement;
        @Ref('gallery')
        readonly gallery!: HTMLDivElement;

        @Prop()
        public images!: DeltaOpsInterface[];

        protected show: boolean = false;
        protected maxImages: number = 4;
        protected imageList: imageAttr = [];
        protected blockStyle: Record<string, string> = {height: 'auto'};
        protected plus: number = 0;

        protected viewImg: string = '';

        protected created(): void {

            if (!this.images.length) {
                return;
            }

            if (this.images.length < this.maxImages) {
                this.maxImages = this.images.length;
            } else {
                this.plus = this.images.length - this.maxImages;
            }

            let loaded = 0;

            for (let i = 0; i < this.maxImages; i++) {

                let src = this.images[i].insert.image;
                // if there is a thumb_url, display thumb
                if (this.images[i].insert.attributes && this.images[i].insert.attributes.thumb_url){
                    src = this.images[i].insert.attributes.thumb_url;
                }

                if (src.match(/\_thumb\.gif$/)) {
                    src = thumbToOrigin(src);
                }

                this.imageList[i] = {
                    src: src,
                    width: 0,
                    height: 0,
                };

                const img = new Image();

                img.onload = () => {
                    this.imageList[i].width = img.width;
                    this.imageList[i].height = img.height;

                    //todo, when image size is small, don't display it as 100% 100%

                    loaded = loaded + 1;

                    if (loaded === this.maxImages) {
                        this.packing();

                        this.show = true;
                    }
                };

                img.src = this.imageList[i].src;
            }

            // bindEvent(window, 'resize', this.packing);
        }

        // protected beforeDestroy() {
        //     removeEvent(window, 'resize', this.packing);
        // }

        protected packing(): void {

            if (this.maxImages === 1) {
                // only one image, just display flex is enough
                return;
            } else if (this.maxImages === 2) {

                this.packTwo(this.imageList);

            } else if (this.maxImages === 3) {

                this.packThree(this.imageList);

            } else if (this.maxImages === 4) {

                this.packFour(this.imageList);
            }
        }

        protected packTwo<T extends imageAttr>(images: T): void {
            const ratio1 = images[0].height / images[0].width;
            const ratio2 = images[1].height / images[1].width;

            // if two horizontal images, only display the first one
//             if (ratio1 < 1.1 && ratio2 < 1.1) {
//                 images.splice(1);
//                 return;
//             }

            const {widthLeft, widthRight, height} = this.calculateWidthHeightByTwoRatio(
                images[0], images[1]);

            images[0].style = {
                width: widthLeft - 1 + 'px',
                height: height + 'px',
                float: 'left',
            };

            images[1].style = {
                width: widthRight - 1 + 'px',
                height: height + 'px',
                float: 'right',
            };

        }

        protected packThree<T extends imageAttr>(images: T): void {

            const areas: number[] = [];

            for (let i = 0; i < this.imageList.length; i++) {
                areas.push(this.imageList[i].width * this.imageList[i].height);
            }

            const max = Math.max(...areas);
            const maxIndex = areas.indexOf(max);

            // move the biggest image to the start
            if (maxIndex !== 0) {
                swapArrayItem(this.imageList, 0, maxIndex);
                swapArrayItem(areas, 0, maxIndex);
            }

            // set same width to the two small images, find corresponding height,
            const [pseudoWidth, pseudoHeight] = this.squeezeTwoBoxes(images[1], images[2]);

            const {widthLeft, widthRight, height} = this.calculateWidthHeightByTwoRatio(
                {width: images[0].width, height: images[0].height}, {width: pseudoWidth, height: pseudoHeight});

            const image1height = Math.floor(height * images[1].height / (images[1].height + images[2].height));
            const image2height = height - image1height;

            this.blockStyle = {height: height + 'px'};

            images[0].style = {
                width: widthLeft - 1 + 'px',
                height: height + 'px',
                float: 'left',
            };

            images[1].style = {
                width: widthRight - 1 + 'px',
                height: image1height - 1 + 'px',
                position: 'absolute',
                top: '0',
                right: '0',
            };

            images[2].style = {
                width: widthRight - 1 + 'px',
                height: image2height - 1 + 'px',
                position: 'absolute',
                top: image1height + 1 + 'px',
                right: '0',
            };
        }

        protected packFour<T extends imageAttr>(images: T): void {

            const [leftWidth, leftHeight] = this.squeezeTwoBoxes(images[0], images[1]);
            const [rightWidth, rightHeight] = this.squeezeTwoBoxes(images[2], images[3]);

            const {widthLeft, widthRight, height} = this.calculateWidthHeightByTwoRatio(
                {width: leftWidth, height: leftHeight},
                {width: rightWidth, height: rightHeight});

            const image0height = Math.floor(height * images[0].height / (images[0].height + images[1].height));
            const image1height = height - image0height;

            const image2height = Math.floor(height * images[2].height / (images[2].height + images[3].height));
            const image3height = height - image2height;

            this.blockStyle = {height: height + 'px'};

            images[0].style = {
                width: widthLeft - 1 + 'px',
                height: image0height - 1 + 'px',
                position: 'absolute',
                top: '0',
                left: '0',
            };

            images[1].style = {
                width: widthLeft - 1 + 'px',
                height: image1height - 1 + 'px',
                position: 'absolute',
                top: image0height + 1 + 'px',
                left: '0',
            };

            images[2].style = {
                width: widthRight - 1 + 'px',
                height: image2height - 1 + 'px',
                position: 'absolute',
                top: '0',
                right: '0',
            };

            images[3].style = {
                width: widthRight - 1 + 'px',
                height: image3height - 1 + 'px',
                position: 'absolute',
                top: image2height + 1 + 'px',
                right: '0',
            };
        }

        /**
         * two boxes, w_1, h_1 and w_2, h_2. set w_2 = w_1; then h_2 = h_2 * w_1 / w_2
         * @param item1
         * @param item2
         */
        protected squeezeTwoBoxes<T extends { width: number, height: number }>(item1: T, item2: T)
            : number[] {
            // set them to the same width,
            const widthSmall = Math.min(item1.width, item2.width);

            // calculate height
            if (item1.width === widthSmall) {
                item2.height = item1.width * item2.height / item2.width;
            } else if (item2.width === widthSmall) {
                item1.height = item2.width * item1.height / item1.width;
            }

            return [widthSmall, item1.height + item2.height];
        }

        /**
         * two boxes, w_1, h_1; w_2, h_2. we know w_1/h_2 = r_1 and w_2/h_2=r_2.
         * set h_1=h_2; then w_1/w_2 = r_2/r_1; and w_1/(w_1+w_2) = 1/(r_1/r_2 + 1)
         * @param ratio1
         * @param ratio2
         */
        protected calculateWidthHeightByTwoRatio<T extends { width: number, height: number }>(item1: T, item2: T)
            : { widthLeft: number, widthRight: number, height: number } {

            const ratio1 = item1.height / item1.width;
            const ratio2 = item2.height / item2.width;

            const cw = this.mainContentWidth();

            // arrange them left and right
            const widthLeft = Math.ceil((1 / ((ratio1 / ratio2) + 1)) * cw);
            const widthRight = cw - widthLeft;

            // they will have the same height
            const height = Math.ceil(widthLeft * ratio1);

            return {widthLeft: widthLeft, widthRight: widthRight, height: height};
        }

        /**
         * We can easily read the width of '.image-block' to get the width of the content,
         * but I didn't take this approach because it only works after all elements are load
         * below calculation is hard to maintain, (adjust each time we change style)
         * but it will render the image elements at the same time as the other elements
         */
        protected mainContentWidth(): number {
            const wh = documentWidth();

            if (IS_MOBILE)
            {
                return wh - 32;
            } else {

                if (this.$route.name === 'thread') {
                    if (wh > 1400) {
                        return 783;
                    } else {
                        return 703;
                    }

                }else{
                    const sideBarWidth = 282;
        
                    if (wh > 1400) {
                        return 780 - 48;
                    } else if (wh > 960) {
                        return 700 - 48;
                    } else if (wh > 700) {
                        return 700 - 24;
                    } else {
                        return wh - 24;  
                    }
                }
            }
        }

        protected onViewImage(src: string) {
            if(this.$route.name !== 'thread') {
                return;
            }

            this.viewImg = src;
        }

        protected closeGallery(e: MouseEvent) {
            if (e.target === this.gallery) {
                this.viewImg = '';
            }
        }

    }
</script>
<style lang="scss" scoped>
    .placeholder {
        @include img_placeholder;
        width: 100%;
        border-radius: $border-radius1;

        &:before {
            content: "";
            display: block;
            padding-top: 60%;
        }
    }

    .image-block {
        position: relative;
        width: 100%;
        border-radius: $border-radius1;
        overflow: hidden;
        max-height: 1200px; /* just in case some one uploaded a very long image */

        .image-box {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;

            img {
                max-width: 100%;
                max-height: 100%;
                width: 100%;
                height: 100%;
                cursor: pointer;
            }

            .plus {
                display: flex;
                align-items: center;
                justify-content: center;
                position: absolute;
                width: 100%;
                height: 100%;
                bottom: 0;
                right: 0;
                background-color: rgba(0, 0, 0, .75);
                color: #ffffff;
                font-size: $font-size3 * 3;
            }
        }

        &.single {

            display: flex;

            .image-box{

                justify-content: flex-start;
                border-radius: $border-radius1;

                img {
                    width: auto;
                    height: auto;
                }
            }
        }
    }

    .gallery {
        position: fixed;
        width: 100vw;
        height: 100vh;
        top: 0;
        left: 0;
        background-color: $mask-color;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: $mask-z-index;

        .img-box {
            border-radius: $border-radius1;
            overflow: hidden;
            // box-shadow: $box-shadow-popup;

            img {
                max-width: 80vw;
                max-height: 90vh;
            }
        }
    }
</style>