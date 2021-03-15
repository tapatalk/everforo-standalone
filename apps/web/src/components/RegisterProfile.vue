<template>
    <section
        :class="['register-profile', {'mobile': isMobile}]"
    >
        <div class="wrapper">
            <div class="title">
                {{$t('complete_profile')}}
            </div>

            <div class="carousel-row">
                <div
                    class="label"
                >{{$t('upload_avatar')}}</div>
                <div 
                    class="carousel-box"
                    ref="carousel"
                >
                    <div 
                        class="carousel-wrapper"
                        ref="wrapper"
                    >
                        <div 
                            :class="['media-carousel-item carousel-item', {'selected': avatarUpload && selectedAvatar == 0}]"
                        >
                            <MediaInput
                                v-on:file-uploaded="onAvatarUpload"
                                v-on:file-cleared="onAvatarCleared"
                                :clear-file="clearAvatar"
                                :defaultMedia="'/img/avatar_camera.png'"
                                :maxAllowedSize=5
                            />
                        </div>
                        <div
                            :class="['carousel-item', {'selected': selectedAvatar == item}]"
                            v-for="item in defaultAvatarTotal"
                            :key="item"
                            v-on:click="selectedAvatar == item? selectedAvatar=-1 : selectedAvatar = item"
                        >
                            <img :src="defaultAvatarPath.replace('{i}', item)" />
                            <div
                                class="avatar-shadow"
                            >
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-if="leftArrowShow && isMobile == false"
                    class="carousel-left-btn"
                    v-on:click="scrollLeft"
                >
                    <a-icon type="left" />
                </div>
                <div
                    v-if="isMobile == false"
                    class="carousel-right-btn"
                    v-on:click="scrollRight"
                >
                    <a-icon type="right" />
                </div>
            </div>

            <div>
                <div
                    :class="['label', 'set-name']"
                >{{$t('set_name')}}</div>
                <a-input
                    :placeholder="$t('username_limit', {min: usernameMinLength, max: usernameMaxLength})"
                    size="large"
                    v-model.lazy="username"
                />
            </div>

            <div>
                <div
                    class="label"
                >{{$t('set_password')}}</div>
                <a-input
                    :placeholder="$t('password_limit', {min: passwordMinLength})"
                    size="large"
                    type="password"
                    v-on:pressEnter="checkProfileData"
                    v-model.lazy="password"
                />
            </div>

            <a-button
                type="primary"
                size="large"
                v-on:click="checkProfileData"
            >{{$t('continue')}}
            </a-button>
        </div>
    </section>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {IS_MOBILE, PASSWORD_MIN_LENGTH, PASSWORD_MAX_LENGTH, USERNAME_MIN_LENGTH, USRENAME_MAX_LENGTH,
    DEFAULT_AVATAR_PATH, DEFAULT_AVATAR_TOTAL, StorageLocal} from "@/helpers/Utils";
    import {UploadedImageUrl} from '@/helpers/Interfaces';
    import {ResponseError} from '@/http/ResponseError';
    import MediaInput from '@/components/MediaInput.vue';

    @Component({
        components: {
            MediaInput,
        },
    })
    export default class RegisterProfile extends Vue {

        @Ref('carousel')
        protected carouselBox!: HTMLDivElement;
        @Ref('wrapper')
        protected wrapper!: HTMLDivElement;

        protected isMobile: boolean = IS_MOBILE;

        public email: string = '';
        protected token: string = '';
        protected go_back: string = '';

        protected username: string = '';
        protected password: string = '';

        protected defaultAvatarPath: string = DEFAULT_AVATAR_PATH;
        protected defaultAvatarTotal: number = DEFAULT_AVATAR_TOTAL;

        protected leftArrowShow: boolean = false;
        protected selectedAvatar: number = -1;
        protected avatarUpload: string | Blob | null = null;
        protected clearAvatar: boolean = false;

        protected usernameMinLength: number = USERNAME_MIN_LENGTH;
        protected usernameMaxLength: number = USRENAME_MAX_LENGTH;
        protected passwordMinLength: number = PASSWORD_MIN_LENGTH;
        protected passwordMaxLength: number = PASSWORD_MAX_LENGTH;

        protected created() {
            // server send a link which include a token and email address to user's email
            this.email = this.$route.query.email as string;
            this.token = this.$route.query.token as string;
            this.go_back = this.$route.query.go_back as string;
        }

        @Watch('selectedAvatar')
        protected onSelectDefaultAvatar(val: number) {
            if (val > 0 && this.avatarUpload){
                this.onClearAvatar();
            }
        }

        /**
         * when file uploaded, get the file object
         * @param fileObject
         */
        protected onAvatarUpload(fileObject: string | Blob): void {
            this.avatarUpload = fileObject;
            this.selectedAvatar = 0;
        }

        /**
         * trigger child component clear file watch
         */
        protected onClearAvatar(): void {
            this.clearAvatar = true;
        }

        /**
         * after child component finished clear file, restore the flag and clear property
         * @param flag
         */
        protected onAvatarCleared(flag: boolean): void {
            if (flag) {
                if (this.avatarUpload){
                    this.avatarUpload = null;
                }
                this.clearAvatar = false;
            }
        }

        // THis is the button clicked handler when users are choosing their avatars
        protected scrollLeft() {
            this.carouselBox.scrollLeft -= 86; // avatar size + margin
            if (this.carouselBox.scrollLeft <= 0) {
                this.leftArrowShow = false;
            }
        }

        // same as above.
        protected scrollRight() {
            const boxWidth = this.carouselBox.getBoundingClientRect().width;
            const wrapperWidth = this.wrapper.getBoundingClientRect().width;

            if (this.carouselBox.scrollLeft >= wrapperWidth - boxWidth) {
                return;
            }
            
            this.carouselBox.scrollLeft += 86; // avatar size + margin
            this.leftArrowShow = true;
        }

        /**
         * In step 2 setup profile, users input their username and password, then register
         * Dismiss modal if succeed
         */
        protected checkProfileData() {
            if (!this.avatarUpload && this.selectedAvatar < 1) {
                this.$message.error(this.$t('upload_avatar_error') as string);
                return;
            }

            if (!this.username || this.username.length < this.usernameMinLength || this.username.length > this.usernameMaxLength) {
                this.$message.error(this.$t('username_error', {min: this.usernameMinLength, max: this.usernameMaxLength}) as string);
                return;
            }

            if (!this.password || this.password.length < this.passwordMinLength || this.password.length > this.passwordMaxLength) {
                this.$message.error(this.$t('password_error', {min: this.passwordMinLength, max: this.passwordMaxLength}) as string);
                return;
            }

            if (!this.token) {
                this.$message.error(this.$t('confirm_expired') as string);
                return;
            }

            this.register();
        }

        protected async register() {

            const data = new FormData();

            data.append('email', this.email);
            data.append('password', this.password);
            data.append('name', this.username);
            data.append('token', this.token);

            if (this.selectedAvatar > 0) {

                const photoUrl = process.env.VUE_APP_DOMAIN + this.defaultAvatarPath.replace('{i}', this.selectedAvatar+'');
                data.append('photo_url', photoUrl);

            } else if (this.avatarUpload) {
                const uploadedAvatarUrl: UploadedImageUrl = await this.$store.dispatch('Profile/uploadAvatar',
                                                                {dataUrl: this.avatarUpload});
                data.append('photo_url', uploadedAvatarUrl.thumb_url);

            } else {
                data.append('photo_url', '');
            }

            this.$store.commit('setShowProgressLine', true);
            this.$store.dispatch('User/registerWithEmail', data)
            .then((data: {token: string} | any) => {
                if (data && data.token) {

                    StorageLocal.setItem('bearer', data.token);
                    this.$store.dispatch('User/getMe')
                    .then(() => {
                        this.$store.commit('setShowLoginModal', false);

                        window.location.href = this.go_back;
                    })
                    .catch((error: ResponseError) => {
                        StorageLocal.removeItem('bearer');
                        this.$message.error(this.$t('login_failed') as string);
                    });
                } else {

                    if (data && data.response && data.response.data ) {
                        if(data.response.data.code == 40010){
                            this.$message.error(this.$t('register_link_expired') as string);

                            this.$store.commit('setShowLoginModal', false);

                            this.$router.push({
                                name: 'homegroups',
                            } as unknown as RawLocation);
                            return;

                        } else if(data.response.data.description){
                            this.$message.error(data.response.data.description);
                        } else {
                            this.$message.error(this.$t('network_error') as string);
                        }

                    } else {
                        this.$message.error(this.$t('network_error') as string);
                    }
                }
            })
            .catch((error: ResponseError) => {
                const response = error.getResponse();
                if (response && response.getStatus() == 422) {
                    const errors = response.getValidationErrors();

                    if (errors){
                        for (let i in errors) {
                            this.$message.error(errors[i] as string);
                        }

                        if (errors.name) {
                            // username taken, do not close
                            return;
                        }
                    }

                    this.$store.commit('setShowLoginModal', false);

                    this.$router.push({
                        name: 'homegroups',
                    } as unknown as RawLocation);
                    return;
                }
            })
            .finally(() => {
                this.$store.commit('setShowProgressLine', false);
            });
        }

    }
</script>
<style lang="scss" scoped>

    .register-profile {
        // .wrapper {
            padding-left: 22%;
            padding-right: 22%;
            margin: 0;
        // }
        .title {
            @include title_font;
            margin-top: 6px;
            margin-right: auto;
            margin-left: auto;
            align-content: center;
            width: fit-content;
        }

        .label {
            @include secondary_title_font();
            margin-top: 30px;
            margin-bottom: 10px;
            padding: 0;
            user-select: none;
            font-weight: 500;
        }

        .carousel-row {
            position: relative;

            .label {
                margin-top: 38px;
            }
        
            .carousel-box {
                height: 100px;
                overflow: hidden;
                width: 348px;

                /* Hide scrollbar for Chrome, Safari and Opera */
                &::-webkit-scrollbar {
                    display: none;
                }

                $avatar-size: 70px;
                $avatar-margin: 16px;

                .carousel-wrapper {
                    display: flex;
                    width: ($avatar-size + $avatar-margin) * 25 - $avatar-margin;

                    .carousel-item {
                        margin-right: var(--p4);
                        cursor: pointer;

                        .media-holder {
                            float: none;
                            width: $avatar-size;
                            height: $avatar-size;
                            border-radius: 50%;
                            overflow: hidden;
                            margin-top: calc(var(--p4) + 6px);
                        }

                        img {
                            z-index: 2;
                            position: relative;
                            top: calc(var(--p4) + 6px);
                            transition: all 0.3s;
                            width: $avatar-size;
                            height: $avatar-size;
                        }

                        .avatar-shadow {
                            display: block;
                            width: 28px;
                            height: 6px;
                            border-radius: 50%;
                            background-color: var(--avatar-shadow-color);
                            margin: var(--p4) auto 0;
                            position: relative;
                            bottom: 0;
                        }

                        &.selected {
                             img {
                                 position: relative;
                                top: 0;
                                transition: all 0.3s;
                                 
                             }
                            .avatar-shadow {
                                display: block;
                                position: relative;
                                bottom: 0;
                                display: block;
                                width: 14px;
                                height: 3px;
                                background-color: var(--avatar-shadow-color);
                                transition: all 0.2s;
                            }
                        }
                    }
                }
                
            }
            .carousel-left-btn, .carousel-right-btn {
                position: absolute;
                top: calc(60% + 1rem);
                cursor: pointer;
                user-select: none;
                -moz-user-select: none;
                -webkit-user-select: none;
            }

            .carousel-left-btn {
                left: -40px;
            }

            .carousel-right-btn {
                right: -40px;
            }
        }

        .ant-btn, .ant-input {
            height: $modal-input-height;
        }

        .ant-btn {
            margin-top: var(--p7);
            width: 100%;
            font-size: 1rem;
        }

        &.mobile {

            padding-left: 0;
            padding-right: 0;

            .label {
                margin-top: 16px;

                &.set-name {
                    margin-top: -12px;
                }
            }

            .carousel-row{
                .label {
                    margin-top: 24px;
                }

                .carousel-box {

                    height: 100px;
                    width: 100%;
                    overflow-y: hidden;
                    overflow-x: scroll;
                    
                    $avatar-size: 50px;
                    $avatar-margin: 8px;

                    .carousel-wrapper {
                        width: ($avatar-size + $avatar-margin) * 25 - $avatar-margin;
                    }

                    .carousel-item {

                        .media-holder {
                            width: $avatar-size;
                            height: $avatar-size;

                        }
                        

                        img {
                            width: $avatar-size;
                            height: $avatar-size;
                        }
                    }
                }
            }
        }
    }
</style>
