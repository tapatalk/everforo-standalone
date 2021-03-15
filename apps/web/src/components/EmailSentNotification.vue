<template>
    <section>
        <div class="wrapper">
            <div
                v-if="type == 1"
                :class="['title',{'mobile':isMobile}]"
            >
                <img src="/img/email-icon.png" />
                <div>{{$t('check_email')}}</div>
            </div>
            <div
                v-else-if="type == 0"
                :class="['title',{'mobile':isMobile}]"
            >
                <img src="/img/question-mark.png" />
                <div>{{$t('here_first_time')}}</div>
            </div>
            <div
                :class="['content',{'mobile':isMobile}]"
            >
                <span 
                    v-html="emailNote"
                ></span>
                <span 
                    v-if="type == 1"
                >
                    <a
                        v-on:click="startOver"
                    >{{$t('start_over')}}</a>
                </span>
            </div>
        </div>
    </section>
</template>
<script lang="ts">
    import {Component, Prop, Emit, Vue} from 'vue-property-decorator';
    import {IS_MOBILE, StorageLocal} from "@/helpers/Utils";

    @Component({
        components: {

        },
    })
    export default class EmailSentNotification extends Vue {
        //0 is email confirm note; 1 is password reset note;
        @Prop()
        protected type!: number;

        @Prop()
        protected email!: string;

        protected emailNote: string = '';

        protected isMobile: boolean = IS_MOBILE;

        protected created(): void {
            this.emailNote = (this.type == 0 ? this.$t('confirm_email', {email: '<b>'+this.email+'</b>'}) : this.$t('reset_password_email', {email: '<b>'+this.email+'</b>'})).toString();
        }

        @Emit('start-over')
        protected startOver() {
            
        }
    }
</script>
<style lang="scss" scoped>
    section {

        .wrapper {
            width: 110%;
        }

        @include modal_flexbox;

        .title {
            text-align: center;
            margin-bottom: var(--p4);

            $s: 80px;

            img {
                width: $s;
                width: $s;
                margin-bottom: var(--p6);
            }

            &.mobile {
                div {
                    font-size: 1.3rem;
                }
            }

            div {
                @include title_font;
                font-size: 1.8rem;
            }
        }

        .content {
            @include secondary_title_font;
            a {
                text-decoration: underline;
            }

            // &.mobile {
                line-height: 1.6rem;
                font-weight: normal;
            // }
        }
    }
</style>