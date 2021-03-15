<template>
    <div :class="[{'mobile': isMobile}]">
        <div class="settings">
            <!-- Group Cover -->
            <div class="label first first-title">{{$t('cover_media')}}:</div>
            <a-row class="item">
                <div
                    class="upload-desc"
                    v-if="isMobile"
                >
                    <span>{{$t('upload_cover_desc')}}</span>
                </div>
                <div class="cover-pic">
                    <MediaInput
                        v-on:file-uploaded="onCoverUpload"
                        v-on:file-cleared="onCoverCleared"
                        width="250px"
                        height="160px"
                        :borderRadius = 8
                        :clear-file="clearCover"
                        :defaultMedia="defaultGroup ? defaultGroup.cover : ''"
                    />
                    <div 
                        v-if="coverUploaded || (defaultGroup && defaultGroup.cover)"
                        v-on:click="onClearCover"
                        class="close"
                        :class="{'mobile':isMobile}"
                    >
                        <Icons type="guanbi"/>
                    </div>
                </div>
                <div 
                    class="upload-desc"
                    v-if="!isMobile"
                >
                    <span>{{$t('upload_cover_desc')}}</span>
                </div>
            </a-row>
            <!-- Group Logo -->
            <div class="label first">{{$t('group_logo')}}:</div>
            <a-row class="item">
                <div 
                    class="upload-desc"
                    v-if="isMobile"
                >
                    <span>{{$t('upload_logo_desc')}}</span>
                </div>
                <div class="logo-pic">
                    <MediaInput
                        v-on:file-uploaded="onLogoUpload"
                        v-on:file-cleared="onLogoCleared"
                        width="120px"
                        height="120px"
                        :borderRadius = 8
                        :clear-file="clearLogo"
                        :defaultMedia="defaultGroup ? defaultGroup.logo : ''"
                    />
                    <div
                        v-if="logoUploaded || (defaultGroup && defaultGroup.logo)"
                        v-on:click="onClearLogo"
                        class="close"
                        :class="{'mobile':isMobile}"
                    >
                        <Icons type="guanbi"/>
                    </div>
                </div>
                 <div 
                    class="upload-desc"
                    v-if="!isMobile"
                >
                    <span>{{$t('upload_logo_desc')}}</span>
                </div>
            </a-row>
            <!-- Group Name -->
            <a-row><div class="label" style="display:inline-block;">{{$t('group_name')}}:</div><span class="lable" style="display:inline-block; text-align:center;vertical-align:sub; margin-left:4px">*</span></a-row>
            <a-row
                class="item group-name"
            >
                <a-input
                    :placeholder="$t('hint_for_group_name', {min: NAME_MIN, max: NAME_MAX})"
                    size="large"
                    :maxLength="NAME_MAX"
                    v-model.lazy="groupName"
                />
<!--                <div -->
<!--                    v-if="!defaultGroup"-->
<!--                    class="desc"-->
<!--                >-->
<!--                    <span>{{$t('group_url_customize_future', {address: groupAddress})}}</span>-->
<!--                    <a        -->
<!--                        v-on:click="showAddressPopup = true"-->
<!--                    >{{$t('customize')}}</a>-->
<!--                </div>-->
<!--                <div-->
<!--                    v-else-->
<!--                    class="desc"-->
<!--                >-->
<!--                    <span>{{$t('group_url_customize_present', {address: groupAddress})}}</span>-->
<!--                </div>-->
            </a-row>
            <a-row><div class="label">{{$t('description')}}:</div></a-row>
            <a-row
                    class="item"
            >
                <a-input
                        :placeholder="$t('hint_for_description', {num: DESC_MAX})"
                        size="large"
                        :maxLength="DESC_MAX"
                        v-model.lazy="groupDesc"
                />
            </a-row>
            <a-row
                v-if="defaultGroup"
            >
                <div class="label category-style">{{$t('categories')}}: ({{$t('optional')}})</div>
                <div 
                    class="categories-desc"
                >
                    <span>{{$t('categories_desc')}}</span>
                </div>
            </a-row>
            <a-row
                    class="item categories"
                    v-if="defaultGroup"
            >
                <a-input
                        size="large"
                        v-for="(category, index) in categories"
                        :key="index"
                        :maxLength="CATE_MAX"
                        v-model="category.name"
                        v-on:keyup.enter="onCategoryEnter(index, category)"
                >
                    <div
                            class="delete-category"
                            slot="addonAfter"
                            v-on:click="deleteCategory(index)"
                    >
                        <Icons
                                type="guanbi"
                        />
                    </div>
                </a-input>
                <div
                    v-if="categories.length < 20"
                    class="add-category"
                    v-on:click="addCategory"
                >
                    <div class="inner">
                        <Icons
                            type="tianjia"
                        />
                    </div>
                </div>
            </a-row>
        </div>
        <a-modal
            :title="$t('group_url_customize_ttile')"
            :visible="showAddressPopup"
            :closable="false"
            :footer="null"
            width="660px"
            v-on:cancel="closeAddressModal"
        >
            <div
                :class="['modal-close-btn', {'mobile': isMobile}]"
                v-on:click="closeAddressModal"
            >
                <Icons
                    type="chacha"
                />
            </div>
            <div
                class='group-address'
            >
                <div class="label">{{$t('group_url')}}</div>
                <div
                    class="item"
                >
                    <span>https://everforo.com/g/</span>
                    <a-input
                        v-model.lazy="groupAddress"
                        :maxLength="NAME_MAX"
                        v-on:change="onAddressChange"
                    ></a-input>
                </div>
                <a-button
                    type="primary"
                    v-on:click="closeAddressModal"
                >
                    {{$t('continue')}}
                </a-button>
            </div>
        </a-modal>
    </div>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue, Watch} from 'vue-property-decorator';
    import {AntVueEvent, CategoryInterface, GroupInterface, UploadedImageUrl} from '@/helpers/Interfaces';
    import MediaInput from '@/components/MediaInput.vue';
    import {IS_MOBILE} from "@/helpers/Utils";
    import DeleteGroupModal from '@/components/DeleteGroupModal.vue';

    interface categoryLike {
        name: string;
        id?: number;
    };

    @Component({
        components: {
            MediaInput,
            DeleteGroupModal,
        },
    })
    export default class GroupForm extends Vue {

        @Prop()
        public defaultGroup!: GroupInterface;
        @Prop()
        public defaultCategories!: CategoryInterface[];
        @Prop()
        public addressPopup!: boolean;
        @Prop()
        public resetButton!: boolean;
        @Prop()
        public submit!: boolean;

        readonly NAME_MIN:number = 3;
        readonly NAME_MAX:number = 50;
        readonly DESC_MIN:number = 1;
        readonly DESC_MAX:number = 300;
        readonly CATE_MAX:number = 50;

        protected isMobile:boolean = IS_MOBILE;
        // uploaded file object
        protected coverUploaded: string | Blob | null = null;
        // uploaded logo object
        protected logoUploaded: string | Blob | null = null;
        // the flag ask child component to clear media preview
        protected clearCover: boolean = false;

        protected clearLogo: boolean = false;
        // group name, not address, address is group name without special characters
        protected groupName: string = '';
        // group address, used in group url
        protected groupAddress: string = '';
        // whether the address is customized, is not, we need to listen to the name change
        protected customizedAddress: boolean = false;
        // a flag to show address popup
        protected showAddressPopup: boolean = false;
        // group description
        protected groupDesc: string = '';
        // categories in the group
        protected categories: categoryLike[] = [];

        protected canSave: boolean = false;
        protected deletegroup: boolean = false;

        protected domain = process.env.VUE_APP_DOMAIN;

        protected adminDisableFeed = false;

        protected superAdminDisableFeed = false;

        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get isAdmin():boolean {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus);
        }

        protected created() {

            // when edit, fill data
            if (this.defaultGroup) {
                this.groupName = this.defaultGroup.title;
                this.groupDesc = this.defaultGroup.description;
                // if 
                this.groupAddress = this.defaultGroup.name;
                // disable address syncing with group title 
                this.customizedAddress = true;

                this.adminDisableFeed = parseInt(this.defaultGroup.no_recommend + '') ? true : false;
                this.superAdminDisableFeed = parseInt(this.defaultGroup.super_no_recommend + '') ? true : false;

                this.canSave = true;
            }

            if (this.defaultCategories && this.defaultCategories.length) {
                for (let i = 0; i < this.defaultCategories.length; i++) {
                    if (this.defaultCategories[i].id > 0) {
                        this.categories.push({
                            name: this.defaultCategories[i].name,
                            id: this.defaultCategories[i].category_id,
                        });
                    }
                }
            }
        }

        protected beforeDestroy(): void {
            this.coverUploaded = null;
            this.logoUploaded = null;
        }

        @Watch('addressPopup')
        protected onAddressPopup(val: boolean) {
            if (val) {
                // trigger group address modal from parent
                this.showAddressPopup = true;
            }
        }

        @Watch('groupName', {immediate: true})
        protected onGroupNameChange(val: string){

            this.canSave = val.length >= this.NAME_MIN && val.length <= this.NAME_MAX;

            if (!this.customizedAddress){
                this.groupAddress = val.toLowerCase().replace(/[^a-z0-9]/g, '');

                this.canSave = this.groupAddress.length >= this.NAME_MIN && this.groupAddress.length <= this.NAME_MAX;
            }
        }

        /**
         * on custom address change
         */
        protected onAddressChange() {
            this.groupAddress = this.groupAddress.toLowerCase().replace(/[^a-z0-9]/g, '');

            this.canSave = this.groupAddress.length >= this.NAME_MIN && this.groupAddress.length <= this.NAME_MAX;
            // once user edited custom address, we no longer listen to `name`
            this.customizedAddress = true;
        }

        protected closeAddressModal() {

            if (this.groupAddress.length == 0) {
                this.groupAddress = this.groupName.toLowerCase().replace(/[^a-z0-9]/g, '');
            }
            // when user already typed a group name, and url is too short ot empty
            if (this.groupAddress.length < this.NAME_MIN && this.groupName) {
                this.$message.info(this.$t('group_url_too_short') as string);
                return;
            }
            
            this.showAddressPopup = false;
        }

        @Watch('resetButton')
        protected onResetButton(val: boolean) {
            if (val) {
                this.canSave = true;
            }
        }

        @Watch('canSave')
        protected onCanSave(val: boolean) {
            this.enableSave();
        }

        @Emit('can-save')
        protected enableSave(){
            return this.canSave;
        }

        @Emit('submit-status')
        protected submitStatus(){

        }

        protected getCategoryName(): categoryLike[] {

            const cats: categoryLike[] = [];

            for (let i = 0; i < this.categories.length; i++) {
                if (this.categories[i].name) {
                    cats.push({id: this.categories[i].id, name: this.categories[i].name});
                }
            }

            return cats;
        }

        /**
         * when file uploaded, get the file object
         * @param fileObject
         */
        protected onCoverUpload(fileObject: string | Blob): void {
            this.coverUploaded = fileObject;
        }

        /**
         * trigger child component clear file watch
         */
        protected onClearCover(): void {
            if (!this.coverUploaded) {
                this.defaultGroup.cover = '';
            }

            this.clearCover = true;
        }

        /**
         * after child component finished clear file, restore the flag and clear property
         * @param flag
         */
        protected onCoverCleared(flag: boolean): void {
            if (flag) {
                if (this.coverUploaded){
                    this.coverUploaded = null;
                }
                this.clearCover = false;
            }
        }

        protected onLogoUpload(fileObject: string | Blob): void{
            this.logoUploaded = fileObject;
        }

        protected onClearLogo(): void {
            if (!this.logoUploaded){
                this.defaultGroup.logo = '';
            }
            this.clearLogo = true;
        }

        protected onLogoCleared(flag: boolean): void {
            if (flag) {
                if (this.logoUploaded){
                    this.logoUploaded = null;
                }
                this.clearLogo = false;
            }
        }

        protected addCategory(): void {
            if (this.categories.length < 20) {
                this.categories.push({name: ''});
            }
        }

        /**
         * delete category
         */
        protected deleteCategory(index: number): void {
            this.categories.splice(index, 1);
        }

        /**
         * add a new empty category input and focus on it
         */
        protected onCategoryEnter(index: number, category: categoryLike) {
            if (category.name) {

                if (index === this.categories.length - 1) {
                    this.addCategory();
                }

                // focus on the next input
                this.$nextTick(() => {
                    const inputs: NodeListOf<HTMLInputElement> = document.querySelectorAll('.categories input');

                    if (inputs[index + 1]) {
                        inputs[index + 1].focus();
                    }
                });
            }
        }

        protected adminDisableFeedSwitch(e: AntVueEvent) {
            this.adminDisableFeed = e.target.checked
        }

        protected superAdminDisableFeedSwitch(e: AntVueEvent) {
            this.superAdminDisableFeed = e.target.checked;
        }

        @Watch('submit')
        protected onSubmit(val: boolean) {
            if (val) {
                this.checkData();
            }
        }


        protected checkData(): void {

            if (!this.groupName) {
                this.$message.info(this.$t('group_name_required') as string);
                this.submitStatus();
                return;
            }

            if (this.groupName.length < this.NAME_MIN || this.groupName.length > this.NAME_MAX) {
                this.$message.info(this.$t('group_name_length', {min:this.NAME_MIN, max:this.NAME_MAX}) as string);
                this.submitStatus();
                return;
            }

            if (this.groupDesc && this.groupDesc.length > this.DESC_MAX) {
                this.$message.info(this.$t('group_desc_length', {min:this.DESC_MIN, max:this.DESC_MAX}) as string);
                this.submitStatus();
                return;
            }

            const categories = this.getCategoryName();
            const usedName: string[] = [];

            for (let i = 0; i < categories.length; i++){

                if (usedName.indexOf(categories[i].name) !== -1) {
                    // do not allow duplicate category
                    this.$message.info(this.$t('duplicate_category_name') as string);
                    this.submitStatus();
                    return;
                }

                usedName.push(categories[i].name);

                if (categories[i].name.length > this.CATE_MAX){
                    this.$message.info(this.$t('category_name_length', {min:1, max:this.CATE_MAX}) as string);
                    this.submitStatus();
                    return;
                }
            }

            this.onSave();
        }

        /**
         * on save we prepare a FormData object, send it to parent component
         */
        @Emit('save')
        protected async onSave(): Promise<FormData> {
            // just a precaution, sometimes this class not get removed
            document.body.classList.remove('no-scroll');

            this.canSave = false;

            const data = new FormData();

            data.append('title', this.groupName);
            data.append('name', this.groupAddress.toLowerCase());
            data.append('description', this.groupDesc ? this.groupDesc : '');
            data.append('no_recommend', this.adminDisableFeed ? '1' : '0');

            const categories = this.getCategoryName();

            if (categories.length) {
                data.append('categories', JSON.stringify(categories));
            } else {
                data.append('categories', JSON.stringify([]));
            }

            if (this.coverUploaded) {
                const uploadedImageUrl: UploadedImageUrl = await this.$store.dispatch('Attachment/uploadGroupPic',
                    {dataUrl: this.coverUploaded});

                data.append('cover', uploadedImageUrl.url);
            } else if (this.defaultGroup) {
                data.append('cover', this.defaultGroup.cover);
            }

            if (this.logoUploaded) {
                const uploadedLogoUrl: UploadedImageUrl = await this.$store.dispatch('Attachment/uploadGroupPic',
                    {dataUrl: this.logoUploaded});

                data.append('logo', uploadedLogoUrl.url);
            } else if (this.defaultGroup) {
                data.append('logo', this.defaultGroup.logo);
            }

            if (this.defaultGroup) {
                data.append('group_id', this.defaultGroup.id + '');
            }

            if (this.$store.state.User.super_admin) {
                data.append('super_no_recommend', this.superAdminDisableFeed ? '1' : '0');
            }

            return data;
        }

    }
</script>
<style lang="scss" scoped>

    .title-box {
        @include form_title;
        padding-bottom: var(--p6);

        .title {
            @include capitalize;
            @include title_font;
        }

        .ant-btn-link {
            @include capitalize;
            padding-right: 0px;

            &.can-save {
                color: var(--theme-color);
                font-weight: 700;
            }
        }
        
    }

    .settings {
        @include clear_after;

        .label {
            @include form_label;
            padding-top: var(--p6);
            padding-bottom: var(--p2);
        }

        // .first-title {
        //     padding-top: var(--p2);
        // }

        .ant-input:focus{
            box-shadow: none;
        }

        .ant-row.item {
            @include form_item;

            &.group-name {
                flex-direction: column;

                .desc {
                    @include description_font;
                    font-size: $upload-desc-font-size;
                }

                a {
                    margin-left: var(--p2);
                    text-decoration: underline;
                    color: var(--theme-color);
                }
            }

            input {
                @include input_style;
                background-color: var(--navbar-bg);
                width: 100%;
            }

            .cover-pic, .logo-pic {
                position: relative;
                margin-right: var(--p6);

                .media-holder {
                    color: #606878;
                    background-color: var(--input-bg);
                    border-radius:8px;
                    border:1px solid var(--border-color2);
                    &:hover {
                        background-color: var(--input-bg);
                    }

                }

                .close {
                    $size: 14px;
                    position: absolute;
                    width: $size;
                    height: $size;
                    top: $size * -1/3;
                    right: $size * -1/3;
                    cursor: pointer;

                    &:hover {
                        .ico {
                            color: var(--font-color1);
                        }
                    }

                    &.mobile {
                        right: 10px;
                    }
                }

            }

            @media (max-width: 1000px) {
                .cover-pic, .logo-pic {
                    @include clear_after();
                }

                .cover-pic {
                    margin-right: 0;
                    margin-bottom: var(--p6);
                }
            }

            &.categories {

                flex-wrap: wrap;

                & > span {
                    width: auto;
                    margin: 0 var(--p6) var(--p6) 0;

                    &:last-of-type {
                        margin-bottom: 0;
                    }
                }

                .delete-category {
                    cursor: pointer;
                    display: block;
                    margin: auto;
                    width: 1em;
                    height: 1em;

                    .ico {
                        vertical-align: initial;
                    }
                }

                .add-category {
                    $s: 40px;
                    display: inline-block;
                    width: $s;
                    height: $s;
                    background-color: var(--input-bg);
                    border: $border-width $border-style var(--border-color3);
                    border-radius: 50%;
                    cursor: pointer;

                    .inner {
                        width: 100%;
                        height: 100%;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    
                        .ico {
                            cursor: pointer;
                            // display: block;
                            // margin: auto;
                            width: 1em;
                            height: 1em;
                        }
                    }
                }
                // &input:focus {
                //     box-shadow: none;
                // }
            }

            &.feed-options {

                flex-direction: column;
                align-items: flex-start;
                justify-content: center;
                margin: 0 0 var(--p6);

                .checkbox {
                    //margin: var(--p2) 0 0 0;
                    @include description_font;
                    display: flex;
                    flex-direction: row;
                    align-items: flex-start;
                    justify-content: center;
                    color: var(--font-color1);

                    .ant-checkbox-wrapper {
                        margin-right: var(--p2);
                    }
                }

            }
        }

        .upload-desc {
            margin-bottom: var(--p3);
            @include upload_desc_font();
        }

        .categories-desc {
            @include upload_desc_font();
            //margin-top: calc(var(--p3) * -1);
            margin-bottom: var(--p3);
        }
    }

    .clickable_link{
        @include description_font;
        text-decoration: underline;
        cursor: pointer;
        display: inline-block;
        margin-top: var(--p2);
        color: var(--font-color6);
    }

    .mobile {
        .settings .ant-row.item {
            input {
                height: 40px;
                margin-bottom: 10px;
            }

            .cover-pic {
                display: inline-block;
                width: 60%;
                padding-right: 5%;
                margin-bottom: 0;

                .media-holder {
                    max-width: 100%;
                    max-height: 100px;
                }
            }

            .logo-pic {
                display: inline-block;
                width: 40%;

                .media-holder {
                    max-width: 100px;
                    max-height: 100px;
                }
            }
        }

        .logo-intro-row .label, 
        .settings .label,
        .group-title-col .ant-btn-link {
            font-size: 15px;
            font-weight: 500;
            line-height: 18px;
        }

        .title-box {
            padding-bottom: 8px;

            .title {
                font-size: 16px;
                font-weight: 500;
            }
        }

    }

    .group-address {

        .label {
            @include form_label;
        }

        .item {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            color: var(--font-color1);
            .ant-input {
                margin-left: var(--p2);
            }
        }

        .ant-btn {
            margin-top: var(--p6);
        }
    }
    .category-style {
        padding-bottom: 8px;
    }
</style>