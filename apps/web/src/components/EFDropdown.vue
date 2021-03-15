<template>
    <div
        :class="['ef-dropdown', {'show-dropdown': showDropdown}]"
        v-on:click="showDropdown = !showDropdown"
    >
        <slot name="left_icon"></slot>
        <span
            :class="['selected-value', {'empty': !defaultValue || !defaultValue.id}]"
        >{{defaultValue ? defaultValue.name : ''}}</span>
        <Icons
            type="xiala"
        />
        <ul
            v-if="showDropdown"
        >
            <li
                v-for="(item, index) in sourceData"
                :key="index"
                v-on:click="onClick(item)"
            >{{item[valueKey]}}
            </li>
        </ul>
    </div>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';

    @Component
    export default class EFDropdown extends Vue {
        @Prop()
        public sourceData!: any[];
        @Prop()
        public valueKey!: string;
        @Prop()
        public defaultValue!: {id: number, name: string};

        protected showDropdown: boolean = false;

        @Emit('select-item')
        protected onClick<T>(item: T): T {
            return item;
        }

    }
</script>
<style lang="scss" scoped>
    .ef-dropdown {
        /* those value is same as .ant-input-lg */
        // $padding-top: 6px;
        $padding-left: 11px;
        $height: 46px;
        $font-size: $font-size1;

        position: relative;
        width: 100%;
        overflow: visible;
        padding: 0 $padding-left 0 0;
        height: $height;
        font-size: $font-size;
        border: $border-width $border-style var(--border-color5);
        border-radius: $border-radius1;

        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;

        .dropdown-left-icon {
            width: $height;
            min-width: $height;
            height: $height;
            border-right: $border-width $border-style var(--border-color1);
        }

        .ico {
            position: absolute;
            right: var(--p6);
            top: 50%;
            margin-top: -8px;
            color: var(--font-color2);
        }

        .selected-value {
            display: inline-block;
            width: 100%;
            height: $height;
            line-height: $height;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-left: $padding-left;

            &.empty {
                color: var(--desc-color);
            }
        }

        ul {
            position: absolute;
            top: $height;
            left: 0;
            width: 100%;
            background-color: var(--body-bg);
            z-index: 2;
            border: $border-width $border-style var(--border-color1);
            border-top-width: 0;
            box-shadow: $box-shadow-popup;
            border-radius: $border-radius1;
        }

        li {
            display: inline-block;
            width: 100%;
            line-height: $height;
            padding: 0 $padding-left;
            @include link_hover;
        }
    }
</style>
