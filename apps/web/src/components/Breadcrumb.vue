<template>
    <a-breadcrumb class="breadcrumb">
        <a-breadcrumb-item>
            <Back
                :route="categoryId 
                ? {name: 'group', params: {group_name: $store.state.Group.name, sort: sortBy, page:1, category_id:categoryId}}
                : {name: 'group', params: {group_name: $store.state.Group.name}}"
            />
            <router-link :to="{name: 'group', params: {group_name: $store.state.Group.name}}">
                <span>{{$store.state.Group.title}}</span>
            </router-link>
        </a-breadcrumb-item>
        <a-breadcrumb-item
            v-if="categoryName"
        >
            <router-link
                    :to="{name: 'group', params: {group_name: $store.state.Group.name, sort: sortBy, page:1, category_id:categoryId}}">
                <span>{{categoryName}}</span>
            </router-link>
        </a-breadcrumb-item>
    </a-breadcrumb>
</template>
<script lang="ts">
    import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
    import {CategoryInterface} from "@/helpers/Interfaces";
    import Back from "@/components/Back.vue";
    import { SORT_BY_GROUP, SORT_BY_THREAD } from '@/helpers/Utils';

    @Component({
        components: {
            Back,
        }
    })
    export default class Breadcrumb extends Vue {
        @Prop()
        public categoryId!: number;

        protected categoryName: string = '';

        get sortBy(): string {
            return this.$route.params.sort ? this.$route.params.sort : SORT_BY_GROUP[1];
        }

        protected beforeDestroy() {
            delete this.onCategories;
        }

        @Watch('categoryId', {immediate: true})
        protected onCategories(id: number): void {
            if (!id) {
                return;
            }

            const categories: CategoryInterface[] = this.$store.state.Category.categories;

            if (!categories.length) {
                return;
            }

            for (let i = 0; i < categories.length; i++) {
                if (categories[i].category_id === id) {
                    this.categoryName = categories[i].name;
                    break;
                }
            }
        }
    }
</script>
<style lang="scss" scoped>
    .breadcrumb {
        padding: var(--p6);
        line-height: $category-line-height;

        .back {
            margin-right: var(--p4);
        }
    }
</style>
