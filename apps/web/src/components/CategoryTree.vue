<template>
    <section
        v-if="categories.length"
    >
        <span class="categories-title">{{$t('categories')}}</span>
        <ul
            class="cat-tree"
            ref='catTree'
            v-if="isAdmin"
        >
            <li
            :class="[{'hover': categoryId === -2}]"
            >
                <router-link :to="{name: 'group', params: {group_name: groupName, sort: sort}}">
                    <span
                            :class="['cat-title', {'hover': categoryId === -2}]"
                    >{{categorySymbol}}{{$t('all_post')}}</span>
                </router-link>
            </li>
            <li
                v-for="category in categories"
                :key="category.id"
                :data-id="category.id"
                v-on:mouseover="showIcon(category)"
                v-on:mouseout="unShowIcon"
                v-on:dragend="dragOrderEnd"
                v-on:dragenter="dragOrderEnter"
                v-on:dragleave="dragOrderLeave"
                :class="[{'hover': categoryId === category.category_id}]"
            >
                <div
                    v-if="category.new_topics"
                    class="new-topics"
                >
                    <div class="dot"></div>
                </div>
                <router-link
                        :to="{name: 'group', params: {group_name: groupName, sort: sort, page: 1, category_id: category.category_id}}">
                    <span
                            :class="['cat-title', {'hover': categoryId === category.category_id}]"
                            :title="category.name"
                    >{{categorySymbol}}{{category.name}}</span>
                </router-link>
                <div
                    v-show="(categories.length > 1 && category.id > 0 && category.id == showIconId)"
                    class="dragger"
                >
                    <Icons
                        type='drag'
                    />
                </div>
            </li>
        </ul>
        <ul
            class="cat-tree"
            ref='catTree'
            v-else
        >
            <li
                :class="[{'hover': categoryId === -2}]"
            >
                <router-link :to="{name: 'group', params: {group_name: groupName, sort: sort}}">
                    <span
                            :class="['cat-title', {'hover': categoryId === -2}]"
                    >{{categorySymbol}}{{$t('all_post')}}</span>
                </router-link>
            </li>
            <li
                v-for="category in categories"
                :key="category.id"
                :class="[{'hover': categoryId === category.category_id}]"
            >
                <div 
                    v-if="category.new_topics"
                    class="new-topics"
                >
                    <div class="dot"></div>
                </div>
                <router-link
                        :to="{name: 'group', params: {group_name: groupName, sort: sort, page: 1, category_id: category.category_id}}">
                    <span
                            :class="['cat-title', {'hover': categoryId === category.category_id}]"
                            :title="category.name"
                    >{{categorySymbol}}{{category.name}}</span>
                </router-link>
            </li>
        </ul>
    </section>
</template>
<script lang="ts">
import {Component, Ref, Vue, Watch} from 'vue-property-decorator';
    import {cloneDeep} from 'lodash';
    import {CategoryInterface} from "@/helpers/Interfaces";
    import {ALL_POST_ID, CATEGORY_SYMBOL, NAV_BAR_HEIGHT, SORT_BY_GROUP, windowHeight} from "@/helpers/Utils";

    @Component
    export default class CategoryTree extends Vue {

        @Ref('catTree')
        readonly catTree!: HTMLUListElement;

        protected categorySymbol: string = CATEGORY_SYMBOL;

        protected dragOrderSwapTarget!: HTMLLIElement;
        protected showIconId: number = 0;

        get groupName(): string {
            return this.$store.state.Group.name;
        }

        get pseudoData() {
            return [1, 2, 3];
        }

        get sort(): string {
            return this.$route.params.sort ? this.$route.params.sort : SORT_BY_GROUP[1];
        }

        get categories(): CategoryInterface[] {
            return this.$store.state.Category.categories;
        }

        set categories(data: CategoryInterface[]) {
            this.$store.commit('Category/setCategories', data);
        }

        get categoryId(): number {
            return this.$route.params.category_id ? parseInt(this.$route.params.category_id) : ALL_POST_ID;
        }

        get adminStatus(): number {
            return this.$store.getters['GroupExtensions/getFeatureStatus']('adminsAndModerators');
        }

        get isAdmin(): boolean {
            return this.$store.getters['User/isSuperAdmin']() || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 1, this.adminStatus) || this.$store.getters['User/isGroupAdmin'](this.$store.state.Group, 2, this.adminStatus);
        }

        protected showIcon(cate:CategoryInterface)
        {
            this.showIconId = cate.id;
            // let cat = this.categories;
            // for (let i = 0; i < cat.length; i++) {
            //             if (cat[i].id === cate.id) {
            //                 cat[i].is_show = true;
            //             }
            //         }
            //         console.log(cat);
            // this.$store.commit('Category/setCategories', cat);
        }

        protected unShowIcon()
        {
            this.showIconId = 0;
        }

        protected mounted() {
            // without the timeout, can't get the element correctly
            setTimeout(() => {
                // the left side bar might be taller than the viewport, calculate sticky top
                this.$watch('categories', function (categoryList: CategoryInterface[]) {
                    if (!categoryList || !categoryList.length) {
                        return;
                    }

                    const sticky = document.getElementById('sticky');

                    if (!sticky) {
                        return;
                    }

                    const top = windowHeight() - sticky.getBoundingClientRect().height - NAV_BAR_HEIGHT;

                    if (top < 0) {
                        // there might be old style, eg. minHeight
                        const old_style = sticky.getAttribute('style');

                        sticky.setAttribute('style', (old_style ? old_style : '') + 'top:' + top + 'px');
                    }

                }, {immediate: true});
            }, 0);
        }

        protected dragOrderEnd(e: DragEvent) {
            const dragTarget = (e.target as HTMLElement).closest('li') as HTMLLIElement;
            
            if (!dragTarget || dragTarget.tagName !== 'LI' || !this.dragOrderSwapTarget || this.dragOrderSwapTarget.tagName !== 'LI') {
                return;
            }

            let dragTargetIndex = -1;
            let dragOrderSwapTargetIndex = -1;
            // swap darg and drop
            for (let i = 0; i < this.categories.length; i++) {
                if (this.categories[i].id == parseInt(dragTarget.dataset.id as string)) {
                    dragTargetIndex = i;
                }

                if (this.categories[i].id == parseInt(this.dragOrderSwapTarget.dataset.id as string)) {
                    dragOrderSwapTargetIndex = i;
                }
            }

            if (this.categories[dragTargetIndex].id < 1 || this.categories[dragOrderSwapTargetIndex].id < 1) {
                return;
            }

            [this.categories[dragTargetIndex], this.categories[dragOrderSwapTargetIndex]] = [this.categories[dragOrderSwapTargetIndex], this.categories[dragTargetIndex]]

            this.categories = cloneDeep(this.categories);

            const categoryOrder = [];

            for (let i = 0; i < this.categories.length; i++) {
                if (this.categories[i].id > 0) {
                    categoryOrder.push(this.categories[i].id);
                }
            }

            const data = new FormData;

            data.append('category_order', JSON.stringify(categoryOrder));

            this.$store.dispatch('Category/order', data);
        }

        protected dragOrderEnter(e: DragEvent) {
            const li = (e.target as HTMLElement).closest('li') as HTMLLIElement;
            li.style.opacity = '.5';
            li.style.border = '1px solid #3d72de';
            this.dragOrderSwapTarget = li;
        }

        protected dragOrderLeave(e: DragEvent) {
            const li = (e.target as HTMLElement).closest('li') as HTMLLIElement;
            li.style.opacity = '';
            li.style.border = 'none';
        }

        @Watch('categoryId')
        protected onCategoryIdChange()
        {
            for (let i = 0; i < this.categories.length; i++) {
                if (this.categories[i].category_id == this.categoryId && this.categories[i].new_topics) {
                    this.categories[i].new_topics = 0;
                }
            }
        }
  
    }
</script>
<style lang="scss" scoped>

    section {
        border-top: $border-width $border-style var(--border-color5);
        padding: var(--p3) 0 var(--p4);
        margin-bottom: 16px;
        .categories-title {
            @include title_font;
            @include capitalize;
            font-size: $font-size2;
        }

        .cat-tree {
            border-bottom: $border-width $border-style var(--border-color5);
            padding: var(--p1) 0 0;
            padding-bottom: 16px;
            overflow: hidden;

            li {
                position: relative;
                
                a {
                    display: inline-block;
                    width: 100%;
                    padding: 0 var(--p6) 0 var(--p4);
                }
                &:hover {
                            z-index: 9997;
                            background-color: var(--hover-bg);
                            color: $theme-color;
                    }
            }

            .new-topics {
                position: absolute;
                width: var(--p4);
                height: 100%;
                top: 0;
                left: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                z-index: -1;
                .dot {
                    width: 4px;
                    height: 4px;
                    z-index: 9998;
                    border-radius: 50%;
                    background-color: rgb(250, 46, 46);
                }
            }

            .cat-title {
                display: inline-block;
                line-height: $category-line-height - 2;
                padding: var(--p2) 0  var(--p2);
                width: 100%;
                // @include category_font;
                font-size: $font-size1;
                color: var(--font-color2);
                // color: #606878;
            }

            .hover {
                background-color: var(--theme-backgroud-color);
                        color: var(--theme-color);
                        z-index: 9999;
                        &:hover {
                            background-color: var(--theme-backgroud-color);
                            color: var(--theme-color);
                    }
            }

            .dragger {
                position: absolute;
                height: 100%;
                top: 0;
                right: var(--p2);
                cursor: pointer;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                z-index: -1;
            }
        }
    }
</style>