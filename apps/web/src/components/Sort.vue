<template>
    <a-row
        type="flex"
        justify="start"
        align="middle"
        class="sort"
    >
        <span class="sort-by">{{$t('filters')}}</span>
        <a-dropdown
            placement="bottomRight"
            :trigger="['click']"
        >
            <a
                class="sort-order"
            >
                <span>{{$t(selectedSort.toLowerCase())}}</span>
                <Icons
                    type="xiala"
                    :class="[{'mobile': isMobile}]"
                />
            </a>
            <a-menu slot="overlay">
                <a-menu-item
                    v-for="(sort, index) in SortByData"
                    :key="index"
                    v-on:click="sortBy(sort)"
                >
                    <span
                        class="sort-order"
                    >{{sort}}</span>
                </a-menu-item>
            </a-menu>
        </a-dropdown>
    </a-row>
</template>
<script lang="ts">
    import {Component, Prop, Emit, Vue, Watch} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {Dictionary} from "vue-router/types/router";
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component
    export default class Sort extends Vue {
        @Prop()
        public selectedSort!: string;

        @Prop()
        public SortByData!: string[];

        @Prop()
        public sortByType!:number;//sort by type is used check memberList or other

        protected selectedSortBy:string = '';//sort by select is used to record sort by change
        protected isMobile: boolean = IS_MOBILE;

        // protected sorts: string[] = this.SortByData;

        // get groupName(): string {
        //     return this.$store.state.Group.name
        // }
        protected created()
        {
            this.selectedSortBy = this.selectedSort;//record first sort
        }
        protected sortBy(sort: string): void {
            //if member list ,can not change route
            if (this.sortByType) {
                if (this.selectedSortBy === sort) {
                    return;
                } else {
                    this.selectedSortBy = sort;
                    this.onSortChange();
                }

            } else {
                const params: Dictionary<string> = this.$route.params;

                if (sort === params.sort) {
                    return
                }
                params.sort = sort;
                this.$router.push({
                    name: this.$route.name,
                    params: params,
                } as unknown as RawLocation);
            }

        }

        @Emit('sort-change')
        public onSortChange(): string
        {
            return this.selectedSortBy;
        }

    }
</script>
<style lang="scss" scoped>

    .sort-order {
        padding: 0 var(--p4) 0 var(--p4);
        font-size: 1em;
        @include capitalize;

    }

    .sort {
        padding: var(--p4) 0;
        font-size: $font-size1;

        .sort-by {
            font-size: 1em;
            color: var(--font-color2);
            @include capitalize;
        }

        .sort-order {
            padding: 0 0 0 var(--p2);
            color: var(--theme-color);
            font-weight: $title-weight;
            display: flex;

            .ico {
                padding-left: var(--p2);
                color: var(--category-color);
                font-size: $font-size0;
                &.mobile {
                    font-size: 0.9rem;
                }
            }
        }
    }

    .post-list {
        .sort {
            .sort-order {
                padding-top: 2px;
            }
        }
    }

</style>