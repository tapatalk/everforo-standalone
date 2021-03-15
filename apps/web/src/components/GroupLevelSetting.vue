<template>
    <FullScreenModal
        v-on:close="onCancel"
        :contentMaxWidth="isMobile? null:'660px'"
        :content-height="'auto'"
        :max-height="'auto'"
        :top="'15%'"
    >
        <template v-slot:header>
           <div
                   :class="['title-icon', {'mobile-title-icon': isMobile}]"
            >
                <a-icon 
                v-on:click="onCancel"
                type="left"
            />
            </div>
            
            <div
                v-if="title"
                class="title"
            >{{title}}</div>
        </template>
        <div
            v-on:click="onCancel"
            :class="['modal-close-btn']"
        >
            <Icons
                type="chacha"
            />
        </div>

        <a-row>
            <div class="label">{{$t('visibility')}}</div>
        </a-row>
        <a-row
            class="item"
        >
            <div
                class="desc"
            >
                {{$t('visibility_desc')}}
            </div>
        </a-row>
        <a-row
                class="checkbox-visib"
        >
            <template>
<!--                <a-radio-group-->
<!--                    v-model="visibility"-->
<!--                    @change="changeVisib"-->
<!--                >-->
<!--                    <a-radio-->
<!--                        v-for="visib in visibilityRadio"-->
<!--                        :key="visib.value"-->
<!--                        :value="visib.value"-->
<!--                        class="redio-style"-->
<!--                    >-->
<!--                        {{visib.label}}-->
<!--                    </a-radio>-->
<!--                </a-radio-group>-->
                <a-checkbox
                    @change="changeVisib"
                    :checked = "checkedStatus"
                >
                    {{$t('only_members_can_view_the_content')}}
                </a-checkbox>
                
            </template>
        </a-row>
        <a-row>
            <div class="label">{{$t('joining')}}</div>
        </a-row>
        <a-row
            class="item"
        >
            <div
                class="desc"
            >
                {{$t('joining_desc')}}
            </div>
        </a-row>
        <a-row
            class="last-row"
        >
            <template>
                <a-radio-group
                    v-model="joining" 
                    @change="changeJoin"
                >
                    <a-radio
                        v-for="join in joiningRadio"
                        :key="join.value"
                        :value="join.value"
                        class="redio-style"
                    >
                        {{join.label}}
                    </a-radio>
                </a-radio-group>
            </template>
        </a-row>

        <!-- <a-button
            type="primary"
            class="button-style"
            v-on:click="onSave"
        >
            {{$t('save')}}
        </a-button> -->

    </FullScreenModal>
</template>
<script lang="ts">
    import {Component, Emit, Vue} from 'vue-property-decorator';
    import {FlagInterface} from '@/helpers/Interfaces';
    import TransferAdmin from '@/components/TransferAdmin.vue';
    import QuestionMark from '@/components/QuestionMark.vue';
    import UserAvatar from '@/components/UserAvatar.vue';
    import Username from '@/components/Username.vue';
    import FullScreenModal from '@/components/FullScreenModal.vue';
    import {IS_MOBILE} from "@/helpers/Utils";

    @Component({
        components: {
            TransferAdmin,
            QuestionMark,
            FullScreenModal,
            UserAvatar,
            Username,
        },
    })
    export default class GroupLevelSetting extends Vue {

        protected title: string = this.$t('GroupLevelPermission') as string;
        protected isMobile: boolean = IS_MOBILE;
        protected joining = 0;
        protected visibility = 0;
        protected radioStyle: any = {
            display: 'block',
            height: '30px',
            lineHeight: '30px',
        };

        protected visibilityRadio = 
            [
                {
                    value : 1, label: this.$t('search_engine_discoverble')
                },
                {
                    value : 3, label: this.$t('only_members_can_view_the_content')
                }
            ];

        protected joiningRadio = [
            {
                value : 1, label: this.$t('everyone_can_join')
            },
            {
                value : 2, label: this.$t('everyone_can_join_but_requires_admin_approval')
            },
            {
                value : 3, label: this.$t('invite_by_admin_and_members_only')
            },
            {
                value : 4, label: this.$t('invite_by_admin_only')
            }
        ];

        get checkedStatus() :boolean
        {
            return this.visibility == 3 ? true : false;
        }

        protected created()
        {
            this.getGroupPrivacy();
        }

        protected getGroupPrivacy()
        {
            this.$store.dispatch('GroupPrivacy/getGroupPrivacy',  {group_id: this.$store.state.Group.id})
                .then((response: any) => {
                    if (response && (response.joining || response.visibility)) {
                        this.joining = response.joining;
                        this.visibility = response.visibility;
                    } else {
                        this.joining = 1;
                        this.visibility = 1;
                    }
                });
        }

        @Emit('close-invite-member')
        protected onCancel() {

        }

        protected changeVisib(e: any) {
            if (e.target.checked) {
                this.visibility = 3;
            } else {
                this.visibility = 1;
            }
            // this.visibility = e.target.value;
            this.onSave();
        }

        protected changeJoin(e: any) {
            this.joining = e.target.value;
            this.onSave();
        }

        protected onSave()
        {
            var formData = new FormData;
            formData.append('group_id', this.$store.state.Group.id);
            formData.append('visibility', this.visibility + '');
            formData.append('joining', this.joining + '');
            this.$store.dispatch('GroupPrivacy/setGroupPrivacy', formData)
                .then((response: any) => {
                    if (response && response.response && response.response.data
                            && response.response.data.code == 403) {
                        this.$message.error(this.$t('no_permission') as string);
                    } else {
                        this.$message.success(this.$t('group_info_updated') as string);
                    }
                });
        }
        
    }
</script>
<style lang="scss" scoped>
    .label {
        @include form_label;
        padding-top: var(--p6);
        padding-bottom: var(--p2);
        button {
            width: 100px;
            border-radius: 4px;
        }
    }
    .redio-style {
        .ant-radio-group {
            label {
                display: block;
            }
        }
    }
    label {
        display: block;
    }
    .item {
        textarea {
            line-height: 25px;
        }
        .desc {
            font-size: 0.9rem;
            color: var(--desc-color);
        }
    }
    .redio-style {
        display: block;
        margin-top: 16px;
    }
    .button-style {
        margin-top: 15px;
        padding-left: 40px;
        padding-right: 40px;
    }
    .mask .content .title {
        text-align: center;
                padding: var(--p4) 0;
                margin: 0 var(--p8) 0 0;
        padding-left: 24px;
    }
    .title-icon {
        position: absolute;
        top: 40px;
        color: var(--font-color1);
    }
    .mobile-title-icon {
        top: 28px;
    }
    .last-row {
        margin-bottom: 24px;
    }
    .checkbox-visib {
        margin-top: 12px;
    }
</style>
<style>
.ant-radio-inner {
    border-color: var(--border-color6);
}
</style>
