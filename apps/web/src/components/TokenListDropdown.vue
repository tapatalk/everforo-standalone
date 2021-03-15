<template>
    <div
        :class="['ef-dropdown', {'show-dropdown': showDropdown}]"
        v-on:click="showDropdown = !showDropdown"
    >
        <slot name="left_icon"></slot>
        <span
            v-if="selectedValue"
            class="selected-value"
        >{{selectedValue}}</span>
        <span
            v-else
            class="selected-value"
        >{{defaultValue}}</span>
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
            >
                <div
                    class='token-item'
                    v-on:click="onClick(item)"
                >
                    <div class="logo">
                        <img
                            v-if="item.logo"
                            :src="item.logo"
                            :alt="item.name"
                        >
                        <img 
                            v-else
                            src="/img/erc20.png" 
                            :alt="item.name"
                        >
                    </div>
                    <div class="name-address">
                        <div class="name">
                            {{item.name}} ({{item.symbol}})
                        </div>
                        <div class="address">
                            {{item.contract_address}}
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>
<script lang="ts">
    import {Component, Emit, Prop, Vue} from 'vue-property-decorator';
    import {TokenInterface} from '@/helpers/Interfaces';

    @Component
    export default class TokenListDropdown extends Vue {
        @Prop()
        public sourceData!: any[];
        @Prop()
        public defaultValue!: any;

        protected showDropdown: boolean = false;

        protected selectedValue: string = '';

        @Emit('select-item')
        protected onClick(item: TokenInterface): TokenInterface {
            this.selectedValue = item.name + '(' + item.symbol + ')';
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
            color: var(--desc-color);
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

            .token-item {
                display: flex;
                flex-direction: row;
                justify-content: flex-start;
                align-items: center;
                cursor: pointer;
                margin: var(--p4) 0;

                .logo {
                    margin-right: var(--p4);
                    img {
                        width: 30px;
                        height: 30px;
                    }
                }

                .name-address {
                    .name {
                        @include content_font;
                    }

                    .address {
                        @include description_font;
                         font-size: $upload-desc-font-size;
                        line-height: 1.3rem;
                    }
                }
            }
        }
    }
</style>
