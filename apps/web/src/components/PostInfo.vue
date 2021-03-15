<template>
    <a-row
            v-if="postsCount || likesCount"
            class="post-info"
            type="flex"
            justify="start"
            align="middle"
            :class="[{'mobile': isMobile}]"
    >
        <div
            v-if="postsCount"
            :class="['action', {'first-post': isFirstPost == false}, {'mobile': isMobile}]"
        >
            <span class="num">{{postsCount}}</span>
            <span class="text"> {{$tc('comment', postsCount)}}</span>
        </div>
        <Dot
            v-if="postsCount && likesCount && isFirstPost == false"
        />
        <div
            v-if="likesCount && isFirstPost == false"
            :class="['action', {'first-post': isFirstPost == false,}, {'mobile': isMobile}]"
        >
            <span class="num">{{likesCount}}</span>
            <span class="text"> {{$tc('likes', likesCount)}}</span>
        </div>
    </a-row>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from "@/helpers/Utils";
    import Dot from '@/components/Dot.vue';

    @Component({
        components: {
            Dot,
        },
    })
    export default class PostInfo extends Vue {
        @Prop()
        public postsCount!: number;
        @Prop()
        public likesCount!: number;
        @Prop()
        public isFirstPost!: boolean;

        protected isMobile: boolean = IS_MOBILE;
    }
</script>
<style lang="scss" scoped>
    .post-info {
        padding: var(--p6) 0 0;

        .action {
            color: var(--font-color1);
            font-size: $font-size2;
            font-weight: 500;
            @include capitalize;

            &.mobile {
                font-size: 1.3rem;
            }
        }

        .action.first-post {
            font-size: 0.9rem;

            .text {
                white-space: pre;
                color: var(--desc-color);
                font-size: 0.9rem;
            }

            &.mobile {
                font-size: $mobile-thread-like-font-size;

                .text {
                    white-space: pre;
                    font-size: $mobile-thread-like-font-size;
                }
            }
        }

        &.mobile {
            padding-top: 24px;
        }
    }
</style>
