<template>
    <section
        class='editor-trigger-section'
    >
        <div 
            class="avatar-box"
            :class="[{'mobile': isMobile}]"
        >
            <UserAvatar 
                :scale="avatarScale"
                :profileId="$store.state.User.id"
            />
        </div>
        <div
            class="topic-editor"
        >
            <div
                class="editor-trigger"
                v-on:click="onClick"
            >
                <span>{{placeholder}}</span>
            </div>
        </div>
    </section>
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from "@/helpers/Utils";
    import UserAvatar from '@/components/UserAvatar.vue';

    @Component({
        components: {
            UserAvatar,
        },
    })
    export default class EditorTrigger extends Vue {
        /**
         * a fake input, when user click, trigger an editor or login/email-confirm poup
         */

        @Prop()
        public triggerFunc!: any;
        @Prop()
        public placeholder!: string;
        @Prop({default: 2})
        public avatarScale!: number;

        protected isMobile: boolean = IS_MOBILE;

        protected onClick(): void {
            // if current user is a guest or inactive user, show login/confirm popup
            if (this.$store.state.User.id && this.$store.state.User.activate) {

                if (typeof this.triggerFunc === 'function') {
                    this.triggerFunc();
                }

                return;
            }

            this.$store.commit('setShowLoginModal', true);
        }

    }
</script>
<style lang="scss" scoped>
    section {
        display: flex;
        flex-flow: row wrap;
        justify-content: center;
        align-items: center;

        .avatar-box {
            order: 1;
            flex-shrink: 0;
            padding-right: var(--p2);

            &.mobile {
                padding-right: var(--p4);
            }
        }

        .topic-editor {
            order: 1;
            flex: 1 1 auto;
            

            .editor-trigger {
                line-height: $avatar-size2;
                text-indent: var(--p6);
                color: var(--desc-color);
                @include input;
                border: $border-width $border-style var(--border-color2);
            }
        }
    }
</style>

