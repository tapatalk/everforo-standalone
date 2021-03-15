<template>
<a-dropdown
                placement="bottomLeft"
                :trigger="['click']"
        >
            <a class="ant-dropdown-link" @click="e => e.preventDefault()">
                <span class="t">{{categorySymbol}}{{currentCategory}}</span>
                <Icons type="xiala"/>
            </a>
    <a-menu 
        slot="overlay"
        v-if="categories.length"
    >

            <a-menu-item>
                <router-link :to="{name: 'group', params: {group_name: groupName, sort: sort}}">
                    <span
                            :class="['cat-title', {'hover': categoryId === allPostID}]"
                    >{{categorySymbol}}{{$t('all_post')}}</span>
                </router-link>
            </a-menu-item>

            <a-menu-item
                    v-for="(category, index) in categories"
                    :key="index"
                    :class="[{'hover': categoryId === category.category_id}]"
            >
                <router-link
                        :to="{name: 'group', params: {group_name: groupName, sort: sort, page: 1, category_id: category.category_id}}">
                    <span
                            :class="['cat-title', {'hover': categoryId === category.category_id}]"
                            :title="category.name"
                    >{{categorySymbol}}{{category.name}}</span>
                </router-link>
            </a-menu-item>
    </a-menu>
    <a-menu
        slot="overlay"
        v-else
        class="cat-tree"
    >

        <a-menu-item>
            <router-link :to="{name: 'group', params: {group_name: groupName, sort: sort}}">
                <span
                        class="cat-title"
                >{{categorySymbol}}{{$t('all_post')}}</span>
            </router-link>
        </a-menu-item>

        <a-menu-item
                v-for="num in pseudoData"
                :key="num"
        >
            <a-skeleton
                    active
                    :paragraph="{rows: 1}"
            />
        </a-menu-item>
    </a-menu>
</a-dropdown>
</template>
<script lang="ts">
    import {Component, Vue, Watch} from 'vue-property-decorator';
    import {CategoryInterface} from "@/helpers/Interfaces";
    import {ALL_POST_ID, CATEGORY_SYMBOL, NAV_BAR_HEIGHT, SORT_BY_GROUP, windowHeight} from "@/helpers/Utils";

    @Component
    export default class CategoryTreeMobile extends Vue {

        protected currentCategory: string = '';
        readonly allPostID = ALL_POST_ID;
        protected categorySymbol: string = CATEGORY_SYMBOL;

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

        get categoryId(): number {
            return this.$route.params.category_id ? parseInt(this.$route.params.category_id) : ALL_POST_ID;
        }

        @Watch('categoryId', {immediate: true})
        protected onCategoryIdChange(): void {
            if (this.categoryId === ALL_POST_ID) {
                this.currentCategory = this.$t('all_post') as string;
            } else {

                // console.log(this.categories);
                // console.log(this.categoryId);
                // console.log(this.categories[this.categoryId-1])
                this.currentCategory = this.categories[this.categoryId-1].name;

                // for (let i = 0; i < this.categories.length; i++) {
                //     if (this.categories[i].id === this.categoryId) {
                //         this.currentCategory = this.categories[i].name;
                //         break;
                //     }
                // }
            }
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
                        sticky.setAttribute('style', 'top:' + top + 'px');
                    }

                }, {immediate: true});
            }, 0);
        }
    }
</script>
<style lang="scss" scoped>

    section {
        padding: var(--p3) 0 var(--p4);

        .categories-title {
            @include title_font;
            @include capitalize;
            font-size: $font-size2;
        }

        .cat-tree {
            border-bottom: $border-width $border-style var(--border-color5);
            padding: var(--p1) 0 0;

            .cat-title {
                display: inline-block;
                line-height: $category-line-height - 2;
                padding: var(--p1-5) var(--p6) var(--p1-5) var(--p2);
                width: 100%;
                @include category_font;
                @include transition(all);
                @include link_hover;
            }
        }
    }
</style>
<style lang="scss">
    .ant-dropdown-menu {
        max-width: 320px;

        .ant-dropdown-menu-item {
            @include link_hover;
        }
        span.cat-title {
            @include link_hover;
        }
    }

    .ant-dropdown-link {
        width: 100%;

        span {
            display: inline-block;
            color: var(--font-color1);
            font-weight: 500;
            margin-right: 0.4rem;
        }

        .ico {
            font-size: 0.9rem;
        }
    }

    .ant-dropdown-menu-item > a, 
    .ant-dropdown-menu-submenu-title > a,
    .ant-dropdown-link span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ant-dropdown-menu-item > a, .ant-dropdown-menu-submenu-title > a {
        color: var(--font-color2);
    }
</style>