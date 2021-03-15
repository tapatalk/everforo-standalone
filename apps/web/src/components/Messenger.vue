<template>
    <div
        v-if="notice || error"
        class="messenger"
    >
        <span
            class="notice"
            v-if="notice"
        >{{notice}}</span>
        <span
            class="error"
            v-if="error"
        >{{error}}</span>
    </div>
</template>
<script lang="ts">
    import {Component, Vue, Watch} from 'vue-property-decorator';

    @Component
    export default class Messenger extends Vue {

        // user notice message
        get notice(): string {
            return this.$store.getters.getNoticeMessage;
        }

        // user error message
        get error(): string {
            return this.$store.getters.getErrorMessage;
        }

        @Watch('$route', {deep: true})
        protected onRouteUpdate(){
            this.$store.commit('setNoticeMessage', '');
            this.$store.commit('setErrorMessage', '');
        }

    }
</script>
<style lang="scss" scoped>
    .messenger {
        position: fixed;
        bottom: var(--p6);
        right: 0;
        padding: var(--p4);
        z-index: $messenger-z-index;
        background-color: var(--body-bg);
        box-shadow: $box-shadow;
        border-radius: $border-radius1 0 0 $border-radius1;

        .notice {
            @include notice_font;
        }

        .error {
            @include error_font;
        }
    }
</style>