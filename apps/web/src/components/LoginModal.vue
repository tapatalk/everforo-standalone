<template>
    <a-modal
        v-model="showLoginModal"
        :width="modalWidth"
        :bodyStyle="modalHeight"
        :maskStyle="{backgroundColor: 'rgba(0,0,0,0.75)'}"
        :closable="false"
        :footer="null"
        :centered="true"
        v-on:cancel="closeModal"
    >
           <div
            v-on:click="closeModal"
            :class="['modal-close-btn', {'mobile': isMobile}]"
        >
                <Icons
                    type="chacha"
                />
        </div>
        <div 
            v-if="step == 3 || step == 4 || step == 1 || (step == 0 && isMobile)"
            class="login-back-btn"
            v-on:click="goBackToStep"
        >
            <Icons type="fanhui"/>
        </div>

        <MobileWebLoginOption
            v-if="step == -1 && isMobile"
            v-on:continue-with-email="continueWithEmail"
        />

        <Onboarding
            v-else-if="step == 0"
            v-on:email-inbound="onEmailInbound"
        />

        <EmailSentNotification
            v-else-if="step == 1"
            :type="0"
            :email="email"
        />

        <RegisterProfile
            v-else-if="step == 2"
        />

        <PasswordLogin
            v-else-if="step == 3"
            :email="email"
            :username="username"
            v-on:reset-password="onResetPassword"
        />        

        <EmailSentNotification
            v-else-if="step == 4"
            :type="1"
            :email="email"
            v-on:start-over="startOver"
        />

        <PasswordReset
            v-else-if="step == 5"
            v-on:success="onPasswordResetSuccess"
        />

    </a-modal>
</template>
<script lang="ts">
    import {Component, Vue, Prop, Watch} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {ResponseError} from '@/http/ResponseError';
    import {IS_MOBILE, StorageLocal} from "@/helpers/Utils";
    import Onboarding from '@/components/Onboarding.vue';
    import EmailSentNotification from '@/components/EmailSentNotification.vue';
    import MobileWebLoginOption from '@/components/MobileWebLoginOption.vue';
    import PasswordLogin from '@/components/PasswordLogin.vue';
    import PasswordReset from '@/components/PasswordReset.vue';
    import RegisterProfile from '@/components/RegisterProfile.vue';
    import {windowWidth} from '@/helpers/Utils';

    @Component({
        components: {
            Onboarding,
            EmailSentNotification,
            MobileWebLoginOption,
            PasswordLogin,
            PasswordReset,
            RegisterProfile,
        },
    })
    export default class LoginModal extends Vue {

        protected isMobile: boolean = IS_MOBILE;
        
        protected email: string = '';
        protected username: string = '';

        // 0 for first login screen; 1 for email confirmation notes; 2 for setup profile step;
        // 3 for use password to login step; 4 for password reset notes;5 for password reset step;
        // -1 is only for mobile, let user choose continue with app or email
        protected step: number = IS_MOBILE ? -1 : 0;

        protected modalWidth: number = 750;
        protected modalHeight = {height: '650px'};
        // this key controls whether we display login modal
        set showLoginModal(flag) {
            this.$store.commit('setShowLoginModal', flag);
        }

        get showLoginModal() {
            return this.$store.state.showLoginModal;
        }

        @Watch('showLoginModal', {immediate: true})
        protected onShowLoginModal(val: boolean) {
            if (val) {
                // If user already registered but not activated yet, then show step 1
                if (StorageLocal.getItem('bearer') && !this.$store.state.User.activate && this.$store.state.User.id) {
                    this.email = this.$store.state.User.email;
                    this.step = 1;
                } else if (this.$route.name === 'passwordreset') { // If user come from password reset link or email confirmation link, then open the login modal
                    this.step = 5; // If login type is register, then open setup profile step; otherwise open password reset step.
                } else if (this.$route.name === 'register') {
                    this.step = 2;
                }
            }
        }

        protected closeModal() {
            this.$store.commit('setShowLoginModal', false);
        }

        @Watch('step', {immediate: true})
        protected onStepChange(val: number) {
            if (this.isMobile) {
                this.modalWidth = windowWidth() * 0.9;
                this.modalHeight = {height: this.step == 2 ? '90%' : '360px'};
            } else if (this.step == 0) {
                this.modalWidth = 660;
                this.modalHeight.height = '500px';
            } else if (this.step == 1) {
                this.modalWidth = 660;
                this.modalHeight.height = '350px';
            } else if (this.step == 2) {
                this.modalWidth = 660;
                this.modalHeight.height = '600px';
            } else if (this.step == 3) {
                this.modalWidth = 660;
                this.modalHeight.height = '437px';
            } else if (this.step == 4) {
                this.modalWidth = 660;
                this.modalHeight.height = '350px';
            } else if (this.step == 5) {
                this.modalWidth = 660;
                this.modalHeight.height = '456px';
            }
        }


        // Same as above, if users choose to continue with email, they will go to step 0 to enter email.
        protected continueWithEmail() {
            this.step = 0;
        }

        protected onEmailInbound(data: {email: string, username: string}) {
            this.email = data.email;
            this.username = data.username;

            if (this.username) {
                this.step = 3;
            } else {
                this.step = 1;
            }
        }

        protected onResetPassword() {
            // show semt sent notification
            this.step = 4;
        }

        protected onPasswordResetSuccess(user_info: {email: string, username: string}) {
            this.email = user_info.email;
            this.username = user_info.username;
            this.step = 3;
        }
        /**
         * In step 4 reset password, if user wants to start over
         * Return to step 1
         */
        protected startOver() {
            this.step = 0;
        }

        // There will be a back button in some of the steps.
        protected goBackToStep() {
            if (this.step == 0 && this.isMobile) {
                this.step = -1;
            } else if (this.step == 3) {
                this.step = 0;
            } else if (this.step == 4) {
                this.step = 3;
            } else if (this.step == 1) {
                this.step = 0;
            }
        }
    }
</script>
<style lang="scss" scoped>
    .login-back-btn {
        position: absolute;
        z-index: 9;
        cursor: pointer;
        top: 28px;
    }
</style>