<template>
    <div>
        <audio ref='ring' src="/audio/notification.mp3"></audio>
        <audio ref='post' src="/audio/post.mp3"></audio>
    </div>
</template>
<script lang="ts">
    import {Component, Ref, Vue} from 'vue-property-decorator';
    import {bindEvent, removeEvent} from '@/helpers/Utils';

    @Component({
        components: {

        }
    })
    export default class NSOD extends Vue {

        @Ref('ring')
        readonly ring!: HTMLAudioElement;
        @Ref('post')
        readonly post!: HTMLAudioElement;

        protected mounted() {
            bindEvent(window, "message", this.playSound);
        }

        protected beforeDestroy() {
            removeEvent(window, "message", this.playSound);
        }

        protected playSound(e: Event | MessageEvent) {
// console.log(e);
            if((e as MessageEvent).data == 'newnotification') {
                const promise = this.ring.play();

                promise.then(() => {})
                .catch((error) => {
                    console.info(error);
                });
            } else if((e as MessageEvent).data == 'newpost') {
                const promise = this.post.play();

                promise.then(() => {})
                .catch((error) => {
                    console.info(error);
                });
            }
        }
    }
</script>
<style lang="scss" scoped>

</style>

