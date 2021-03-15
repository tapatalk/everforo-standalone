<template>
    <section class="group-card">
        
    
        <div
                class="group-cover"
                v-if="groupIcon"
        >
            <ImageHolder
                    :src="groupIcon"
            />
        </div>
        <div
        v-if="isOwner"
        v-on:click="showManageGroup">
            <Icons
                type="manage_icon.png"
                class="manage-icon"
            />
        </div>
        <div
                class="title-desc"
                v-on:click="descClick"
        >
            <p class="title">{{groupTitle}}</p>
            <p class="desc"
               :class="['desc', {'desc-click': showDesc}]"
            >{{groupDesc}}</p>
        </div>
    </section>
</template>
<script lang="ts">
    import {Component, Vue, Emit} from 'vue-property-decorator';
    import ImageHolder from '@/components/ImageHolder.vue';

    @Component({
        components: {
            ImageHolder,
        }
    })
    export default class GroupCardMobile extends Vue {
        protected showDesc:boolean = true;
        get groupIcon(): string {
            return this.$store.getters['Group/cover'];
        }

        get groupTitle(): string {
            return this.$store.state.Group.title;
        }

        get groupDesc(): string {
            return this.$store.state.Group.description;
        }
        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get isOwner(): boolean {
            return this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus);
        }

        @Emit('show-manage-group')
        protected showManageGroup(){
            // console.log('clicked');
        }

        protected descClick()
        {
            if (this.showDesc) {
                this.showDesc = false;
            } else {
                this.showDesc = true;
            }
        }

    }
</script>
<style lang="scss" scoped>
    .group-card {
        position: relative;

        .group-cover {
            // height: 300px;
            // background: #ffffff;
            background: #000;
            display: flex;
            height: 240px;
            overflow: hidden;
            justify-content: center;
            align-items: center;
            img {
                width: auto;
                opacity: .9;
                filter: alpha(opacity=90);
                height: 100%;
                display: block;
            }
        }

        .title-desc {
            position: absolute;
            padding: 0 var(--p6);
            width: 100%;
            bottom: 0;
            left: 0;

            .title {
                @include title_font;
                color: $font-color-contrast;
                margin-bottom: var(--p4);
                text-shadow:  1px 0 5px rgba($color: (#000000), $alpha: 0.4);
            }

            .desc {
                @include category_font;
                color: $font-color-contrast;
                text-shadow: 1px 0 3px rgba($color: (#000000), $alpha: 0.4);
                line-height: 20px;
            }
            .desc-click {
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }
        }

        .manage-icon {
            position: absolute;
            right: 16px;
            top: 16px;
            width: 24px;
            height: 24px;
        }
    }
</style>
