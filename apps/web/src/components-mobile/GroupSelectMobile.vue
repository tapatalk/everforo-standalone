<template>
    <a-row
            type="flex"
            justify="start"
            align="middle"
            class="group-select"
    >
    
        
            <CategoryTreeMobile/>
    </a-row>
</template>
<script lang="ts">
    import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
    import {RawLocation} from "vue-router";
    import {SORT_BY_GROUP, SORT_BY_THREAD} from '@/helpers/Utils';
    import {Dictionary} from "vue-router/types/router";
    import CategoryTreeMobile from '@/components-mobile/CategoryTreeMobile.vue';

    @Component({
        components: {
            CategoryTreeMobile
        }
    })
    export default class GroupSelectMobile extends Vue {
        @Prop()
        public selectedGroup!: string;

        protected sorts: string[] = (this.$store.state.User.id && this.$store.state.User.activate) ? SORT_BY_GROUP :
                SORT_BY_THREAD;

        // get groupName(): string {
        //     return this.$store.state.Group.name
        // }

        protected sortBy(sort: string): void {

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
</script>
<style lang="scss" scoped>

    .sort-order {
        padding: 0 var(--p4) 0 var(--p4);
        font-size: 1em;
        @include capitalize;
    }

    .group-select {
        font-size: $font-size1;
        width: 60%;

        .sort-by {
            font-size: 1em;
            color: var(--font-color2);
            @include capitalize;
        }

        .sort-order {
            padding: 0 0 0 var(--p2);
            color: var(--theme-color);
            font-weight: $title-weight;

            .ico {
                padding-left: var(--p2);
                color: var(--font-color2);
            }
        }
    }

</style>