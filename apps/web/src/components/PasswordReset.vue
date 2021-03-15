<template>
    <section
        :class="['password-reset', {'mobile': isMobile}]"
    >
        <div
            class='wrapper'
        >
            <div class="title">
                {{$t('reset_password')}}
            </div>
            <div>
                <a-input
                    :placeholder="$t('new_password')"
                    size="large"
                    type="password"
                    v-on:pressEnter="savePassword"
                    v-model.lazy="password"
                />
            </div>
            <div>
                <a-input
                    :placeholder="$t('confirm_password')"
                    size="large"
                    type="password"
                    v-on:pressEnter="savePassword"
                    v-model.lazy="confirmPassword"
                />
            </div>
            <a-button
                type="primary"
                size="large"
                v-on:click="savePassword"
            >{{$t('continue')}}
            </a-button>
        </div>
    </section>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
    import {IS_MOBILE, PASSWORD_MIN_LENGTH, PASSWORD_MAX_LENGTH} from '@/helpers/Utils';

    @Component({
        components: {

        },
    })
    export default class PasswordReset extends Vue {

        protected isMobile: boolean = IS_MOBILE;

        protected email: string = '';
        protected token: string = '';

        protected password: string = '';
        protected confirmPassword: string = '';

        protected passwordMinLength: number = PASSWORD_MIN_LENGTH;
        protected passwordMaxLength: number = PASSWORD_MAX_LENGTH;

        protected created() {
            // server send a link which include a token and email address to user's email
            this.email = this.$route.query.email as string;
            this.token = this.$route.query.token as string;
        }

        protected savePassword() {

            if (!this.email || !this.token) {
                this.$message.error('wrong link');
                return;
            }

            if (!this.password || this.password.length < this.passwordMinLength || this.password.length > this.passwordMaxLength) {
                this.$message.error(this.$t('password_error', {min: this.passwordMinLength, max: this.passwordMaxLength}) as string);
                return;
            }

            if (this.password !== this.confirmPassword) {
                this.$message.error(this.$t('confirm_password_error') as string);
                return;
            }

            this.$store.commit('setShowProgressLine', true);

            const data = new FormData();

            data.append('email', this.email);
            data.append('token', this.token);
            data.append('password', this.password);
            
            this.$store.dispatch('User/resetPassword', data)
            .then((data: any) => {
                if (data.success) {
                    this.passwordResetSuccess(data.success);
                } else {
                    this.$message.error(this.$t('network_error') as string);
                }
            })
            .finally(() => {
                this.$store.commit('setShowProgressLine', false);
            });
        }

        @Emit('success')
        protected passwordResetSuccess(username: string): Record<string, string> {
            return {username: username, email: this.email};
        }
        
    }
</script>
<style lang="scss" scoped>

    .password-reset {

        @include modal_flexbox;

        .title {
            @include title_font;
            margin-bottom: var(--p4);
        }

        .ant-btn, .ant-input {
            width: 100%;
            height: 50px;
        }

        .ant-btn {
            margin-top: var(--p8);
        }

        .ant-input {
            margin-top: var(--p4);
        }
    }
</style>
