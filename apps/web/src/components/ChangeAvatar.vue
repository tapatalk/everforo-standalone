<template>

    <VueScrollBar
            :max-height="isMobile ? 200 : 300"
            :min-height="0"
    >
        <section
                :class="['register-profile', {'mobile': isMobile}]"
        >
            <div class="carousel-row">

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
                                :maxAllowedSize="5"
                                :no-preview="true"
                            />
                        </div>
                        <div
                                v-if="defaultAvatarUpload"
                                :class="['carousel-item', {'selected': selectedAvatar == defaultAvatarUpload}]"
                                v-on:click="selectedAvatar == defaultAvatarUpload ? selectedAvatar=-1 : selectedAvatar = defaultAvatarUpload"
                        >
                            <img class="default-image" :src="defaultAvatarUpload" />
                            <div
                                    class="avatar-shadow"
                            >
                            </div>
                            <div
                                    class="guanbi-class"
                                    v-on:click="deleteUpload"
                            >
                                <Icons
                                        type="guanbi"
                                />
                            </div>
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
            </div>

        </section>
    </VueScrollBar>

</template>
<script lang="ts">
    import {Component, Emit, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
    import {IS_MOBILE, DEFAULT_AVATAR_PATH, DEFAULT_AVATAR_TOTAL, StorageLocal} from "@/helpers/Utils";
    import {UploadedImageUrl, UserInterface} from '@/helpers/Interfaces';
    import MediaInput from '@/components/MediaInput.vue';
    import VueScrollBar from '@/components/VueScrollBar.vue';


    @Component({
        components: {
            MediaInput,
            VueScrollBar
        },
    })
    export default class ChangeAvatar extends Vue {

        @Ref('carousel')
        protected carouselBox!: HTMLDivElement;
        @Ref('wrapper')
        protected wrapper!: HTMLDivElement;

        protected isMobile: boolean = IS_MOBILE;

        protected username: string = '';
        protected password: string = '';

        protected defaultAvatarPath: string = DEFAULT_AVATAR_PATH;
        protected defaultAvatarTotal: number = DEFAULT_AVATAR_TOTAL;

        protected leftArrowShow: boolean = false;
        protected selectedAvatar: any = -1;
        protected avatarUpload: string | Blob | null = null;
        protected clearAvatar: boolean = false;
        protected defaultAvatarUpload: any | boolean = false;
        protected saveDisable = false;

        @Prop()
        public maxHeight!: number;
        @Prop()
        public minHeight!: number;

        @Prop()
        public saveAvatar!: boolean;

        protected created() {
            // server send a link which include a token and email address to user's email
            //console.log(this.$store.state.User.photo_url);
            if (this.$store.state.User.photo_url) {
                let str = this.$store.state.User.photo_url.match(/default_avatar_(.*).png/);
                if (str) {
                    this.selectedAvatar = str[1];
                } else {
                    this.defaultAvatarUpload = this.$store.state.User.photo_url;
                    this.selectedAvatar = this.$store.state.User.photo_url;
                }
                // console.log(this.selectedAvatar);
                this.saveDisable = true;
            }

        }

        @Watch('selectedAvatar')
        protected onSelectDefaultAvatar(val: number) {
            if (!this.saveDisable) {
                if (val > 0 && this.avatarUpload){
                    this.onClearAvatar();
                }

                if(val != -1){
                    this.avatarCanSave(true);
                } else {
                    this.avatarCanSave(false);
                }
            } else {
                this.saveDisable = false;
                this.avatarCanSave(false);
            }

        }


        @Watch('saveAvatar')
        protected onSaveAvatar(bol: boolean){

            if(bol){
                this.checkProfileData();
            }
        }

        @Emit('avatar-can-save')
        protected avatarCanSave(flag: boolean){
            return  flag ? true : false;
        }

        @Emit('avatar-saved')
        protected avatarSaved(){
            return true;
        }

        /**
         * when file uploaded, get the file object
         * @param fileObject
         */
        protected onAvatarUpload(fileObject: string | Blob): void {
            this.defaultAvatarUpload = fileObject;
            this.selectedAvatar = fileObject;
            this.avatarUpload = fileObject;
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

        protected deleteUpload()
        {
            this.defaultAvatarUpload = false;
        }

        protected checkProfileData() {
            if (!this.avatarUpload && this.selectedAvatar < 1) {
                this.$message.error(this.$t('upload_avatar_error') as string);
                return;
            }
            this.updateAvatar();
        }

        protected async updateAvatar() {
            const data = new FormData();

            if (this.selectedAvatar) {
                if (Number.isInteger(this.selectedAvatar)){
                    const photoUrl = process.env.VUE_APP_DOMAIN + this.defaultAvatarPath.replace('{i}', this.selectedAvatar+'');
                    data.append('photo_url', photoUrl);
                } else if (this.selectedAvatar.length < 1000) {
                    data.append('photo_url', this.selectedAvatar);
                } else {
                    const uploadedAvatarUrl: UploadedImageUrl = await this.$store.dispatch('Profile/uploadAvatar',
                            {dataUrl: this.selectedAvatar});
                    data.append('photo_url', uploadedAvatarUrl.thumb_url);
                }

            } else if (this.avatarUpload) {
                const uploadedAvatarUrl: UploadedImageUrl = await this.$store.dispatch('Profile/uploadAvatar',
                                                                {dataUrl: this.avatarUpload});
                data.append('photo_url', uploadedAvatarUrl.thumb_url);

            } else {
                data.append('photo_url', '');
            }

            try {
                const response = await this.$store.dispatch('Profile/update', data);
                const profileData: UserInterface = response.getData();

                if ( response.getCode() != 20000 ){
                    if(response.getDescription()){
                        this.$message.error(response.getDescription() as string);
                    } else {
                        this.$message.error(this.$t('network_error') as string);
                    }
                }

                if (profileData && profileData.id == this.$store.state.User.id){
                    this.$store.commit('User/setCurrentUser', profileData);
                    this.$store.commit('Profile/updateAvatar', profileData.photo_url);
                    this.$message.success(this.$t('profile_updated') as string);
                    this.avatarSaved();
                }

            }catch(e) {

                if (e.response) {
                    const data = e.response.response && e.response.response.data;
                    if (data.errors) {
                        for (const error in data.errors) {
                            this.$message.error(data.errors[error]);
                        }
                    }
                }
            }
        }

    }
</script>
<style lang="scss" scoped>

    .register-profile {

        overflow: hidden;

        .label {
            @include secondary_title_font();
            margin-top: 30px;
            margin-bottom: 10px;
            padding: 0;
            user-select: none;
            font-weight: 500;
        }

        .carousel-row {
            /*position: relative;*/

            .label {
                margin-top: 38px;
            }
        
            .carousel-box {
                
                /* Hide scrollbar for Chrome, Safari and Opera */
                &::-webkit-scrollbar {
                    display: none;
                }

                $avatar-size: 70px;
                $avatar-margin: 16px;

                .carousel-wrapper {

                    display: flex;
                    flex-wrap: wrap;
                    align-content:flex-start;
                    overflow: hidden;

                    .carousel-item {
                        position: relative;
                        margin-right: var(--p4);
                        cursor: pointer;

                        margin-bottom: var(--p1-5);

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

                        img, .avatar-shadow, .guanbi-class {
                            transition: all 0.3s;
                        }

                        &.selected {
                            img {
                                position: relative;
                                top: 0;
                            }
                            .avatar-shadow {
                                display: block;
                                position: relative;
                                bottom: 0;
                                display: block;
                                width: 14px;
                                height: 3px;
                                background-color: var(--avatar-shadow-color);
                            }
                        }
                    }
                }
                
            }
            .carousel-left-btn, .carousel-right-btn {
                position: absolute;
                top: calc(50% + 1rem);
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

                    /*height: 100px;*/
                    width: 100%;
                    overflow-y: hidden;
                    overflow-x: scroll;
                    
                    $avatar-size: 50px;
                    $avatar-margin: 8px;

                    .carousel-wrapper {
                        display: flex;
                        flex-wrap: wrap;
                        align-content:flex-start;
                        overflow: hidden;
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
    .default-image {
        border-radius: 50%;
    }
    .guanbi-class {
        display: inline-block;
        position: absolute;
        right: 0;
        top: 23%;
        z-index: 2;
    }
    .selected {
        .guanbi-class {
            display: inline-block;
            position: absolute;
            right: 0;
            top: 0;
            z-index: 2;
        }
    }
</style>
