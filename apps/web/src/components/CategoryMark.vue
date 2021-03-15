<template>

            <div>
                <span><Icons type="category_tringle"/></span>
            <span>{{category}}</span>
            </div>
        
</template>
<script lang="ts">
    import {Component, Prop, Vue} from 'vue-property-decorator';
    import {IS_MOBILE} from '@/helpers/Utils';

    @Component
    export default class CategoryMark extends Vue {
        @Prop()
        public category!: string;

        protected isMobile: boolean = IS_MOBILE;
        
        protected created(){
            // console.log(this.category);
        }
    }
</script>

<style lang="scss" scoped>

    .category-mask {
        // align-content: center;
        text-align: center;
        
        display: inline-block;

        @include info_font;

        &.mobile {
            @include mobile_thread_time_font;
        }

        .icon {
            margin-top: 10px;
            display: inline-block;
            margin-left: var(--p2);
            width: 7px;
            height: 10px;
            
            .ico {
                width: 7px;
                height: 10px;
            }
        }

        span {
            margin-left: var(--p1);
        }
    }
</style>