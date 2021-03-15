<template>
    <a-row
        type="flex"
        justify="start"
        align="middle"
        class="sort"
        :class="[{'mobile': isMobile}]"
    >
        <span class="sort-by">{{$t('filters')}}</span>
        <a-dropdown
            placement="bottomRight"
            :trigger="['click']"
        >
            <a
                class="sort-order"
            >
                <span>{{myGroups ? $t('participated') : $t('all_groups')}}</span>
                <Icons 
                    type="xiala"
                    :class="[{'mobile': isMobile}]"
                />
            </a>
            <a-menu slot="overlay">
                <a-menu-item v-on:click="filter(false)">
                <span class="sort-order">{{$t('all_groups')}}</span>
                </a-menu-item>
                <a-menu-item v-on:click="filter(true)">
                <span class="sort-order">{{$t('participated')}}</span>
                </a-menu-item>
            </a-menu>
        </a-dropdown>
    </a-row>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from '@/helpers/Utils';
    import {RawLocation} from "vue-router";

    @Component
    export default class ThreadFilter extends Vue {
        protected myGroups: boolean = false;
        protected isMobile: boolean = IS_MOBILE;

        // @Emit("filter-threads")
        protected filter(isMy: boolean) {
            this.myGroups = isMy;
            const parameters = {
                page: 1,
                filter: 0,
            };
            if(isMy){
                parameters.filter = 1;
            }
            this.$router.push({
                name: 'homegroups',
                params: parameters
            } as unknown as RawLocation);

            return this.myGroups;
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
        padding-top: 0.2rem;
        font-size: $font-size1;
        display: inline-block;
        float: right;

        &.mobile {
            padding-top: 0;
            font-size: 1rem;
        }

        .sort-by {
            font-size: 1em;
            color: var(--font-color2);
            @include capitalize;
        }

        .sort-order {
            padding: 0 0 0 var(--p2);
            color: var(--theme-color);
            font-weight: $title-weight;
            display: inline-block;

            .ico {
                padding-left: var(--p2);
                color: var(--category-color);
                vertical-align: -0.1rem;
                font-size: $font-size0;
                &.mobile {
                    font-size: 0.9rem;
                    margin-right: 16px;
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