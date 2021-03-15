<template>
    <div
        :style="defaultStyle"
        class="online-flag"
    ></div>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import UserAvatar from '@/components/UserAvatar.vue';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
            UserAvatar,
        }
    })
    export default class OnlineButton extends Vue {
        protected isMobile: boolean = IS_MOBILE;
        @Prop()
        public buttonSize!: number;
        @Prop()
        public buttonRadius!: number;
        protected defaultStyle = {width: '10px',
                    height: '10px',
                    borderRadius : '5px'};
        protected created() {
            this.defaultStyle = {
                    width: this.buttonSize + 'px',
                    height: this.buttonSize + 'px',
                    borderRadius : this.buttonRadius + 'px'
                };
        }
    }
</script>
<style lang="scss" scoped>
    .online-flag {
        width: 10px;
        height: 10px;
        background-color: #00D642;
        position: absolute;
        right: 1px;
        bottom: 0px;
        border-radius: 5px;
        border: 1px solid var(--body-bg);
    }

</style>