<template>
<div :style="{position: 'relative', height: windowHeight}">
    <HomeNavBar />
    <iframe :style="{height: contentHeight}" src="https://docs.google.com/document/d/e/2PACX-1vRaAln0D0Ekbvk78nqmBC_WkTXqnfvBvOXwUOl4y7OAkJ0F0hvf5akQOaUsQy8-AV3ez-CsJUYhv84o/pub?embedded=true"></iframe>
    <Footer />
</div>
</template>
<script lang="ts">
    import {Component, Vue} from 'vue-property-decorator';
    import { windowHeight } from '@/helpers/Utils';
    import HomeNavBar from '@/components/HomeNavBar.vue';
    import Footer from '@/components/Footer.vue';

    @Component({
        components: {
            HomeNavBar,
            Footer,
        },
    })
    export default class PrivacyPolicy extends Vue {

        protected contentHeight:string = (windowHeight() - 160) + 'px';
        protected windowHeight:string = windowHeight() + 'px';

    }
</script>
<style lang="scss" scoped>

    iframe {
        border: none;
        margin: 0 auto;
        display: block;
        width: 126%;
        margin-left: -13%;
        margin-top: 15px;
    }

    .footer {
        background: var(--theme-color);
        position: absolute;
        padding: 30px 0;
        bottom: 0;
        width: 100%;
    }

</style>
