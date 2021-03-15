export interface AirdropRuleInterface {
    id: number;
    rule_name: string;
    award_count: number;
    action: string;
    condition: string;
    repeat: number;
    total_count?: number;
    exec_status?: number;
}

export interface AntVueEvent {
    nativeEvent: Event;
    preventDefault: object;
    stopPropagation: object;
    target: {checked: boolean, defaultChecked: boolean}
}

export interface AssetInterface {
    id: number;
    token_id: number;
    balance: number;
    name: string;
    symbol: string;
    logo: string;
    is_import: number;
    decimal?: number;
}

export interface CategoryInterface {
    id: number;
    group_id: number;
    name: string;
    category_id: number;
    new_topics?: number;
    is_show?: boolean;
    // children?: any[];
    // [propName: string]: any;
}

export interface DeltaInterface {
    ops: DeltaOpsInterface[];
}

export interface DeltaOpsInterface {
    insert?: any;
    attributes?: any;
    delete?: any;
    format?: any;
}

export interface ERC20TokenInterface {
    id: number;
    group_id: number;
    name?: string;
    logo?: string;
    balance?: number | string;
    decimal?: number | string;
    address?: string;
    contract_url?: string;
    symbol?: string;
    status?: number;
    is_import?: number;
    order_id?: string;
    created_at?: string;
    updated_at?: string;
}

export interface FlagInterface {
    id: number;
    post_id: number;
    user_id: number;
    reason?: number;
    is_ban?: number;
    created_at?: string;
    user?: ProfileInterface;
    poster?: ProfileInterface;
    reason_msg?: string;
    online?: boolean;
}

export interface GroupInterface {
    id: number;
    name: string;
    title: string;
    created_at: string;
    owner: 0;
    cover: string;
    logo: string;
    updated_at: string;
    description: string;
    members?: number;
    online_members?: number;
    threads?: number;
    no_recommend?: number;
    super_no_recommend?: number;
    erc20_token?: ERC20TokenInterface;
    feature?:GroupFeatureInterface[];
    attached_files?: any,
    group_admin?: any,
    joining?: number,
    visibility?: number,
}

export interface GroupFeatureInterface {
    id: number,
    feature_name: string;
    status: string;
}

export interface LikeInterface {
    post_id: number;
    user_id: number;
    is_ban: number;
    user?: ProfileInterface;
    is_subscribe?: number;
    online?: boolean;
}

export interface NotificationInterface {
    id: number;
    type: string;
    thread_id: number;
    post_id: number;
    user_id: number;
    group_name: string;
    user?: { id: number, name: string, photo_url: string };
    token?: {name: string, logo: string}
    created_at: string;
    msg: string;
    title?: string;
    url?: string;
    post_content?: string;
    short_content?: string;
    post_parent_id?: number;
    content: string;
    reason?: number;
    reason_msg?: string;
    likes?: [];
    flags?: [];
    ipfs?: string;
    thread_slug?: string;
    is_ban: number;
    online?: boolean;
    attached_files?: any[];
}

export interface PostInterface {
    id: number;
    thread_id: number;
    parent_id: number;
    user_id: number;
    user: UserInterface;
    created_at: string;
    content: string;
    ipfs: string;
    likes: LikeInterface[];
    flags: FlagInterface[];
    nsfw?: number;
    children?: PostInterface[];
    deleted?: number;
    deleted_by?: ProfileInterface;
    is_new?: boolean;
    is_ban?: number;
    group_id?: number;
    total_likes?: number;
    total_report?: number;
    is_subscribe?:boolean;
    attached_files?: any[];
}

export interface ProfilePostInterface {
    deleted: number;
    deleted_by: number;
    group_id: number;
    group_name: string;
    group_post_id: number;
    group_thread_id: number;
    id: number;
    ipfs: string;
    like_username?: string;
    nsfw: number;
    nsfw_score: number;
    parent_id: number;
    thread_id: number;
    thread_poster_id: number;
    thread_poster_name: string;
    thread_title: string;
    created_at: string;
    updated_at: string;
    user_avatar: string;
    user_id: number;
    username: string;
    parent_poster_name: string;
}

export interface ProductInterface {
    id: number;
    price: string;
    product_name: string;
    status: number;
}

export interface ProfileInterface {
    user_id: number;
    name: string;
    photo_url: string;
    is_ban?: number;
    last_seen?:boolean;
    online?: boolean;
}

export interface RequestOptionsInterface {
    route: string;
    param?: Record<string, any>;
    headers?: Record<string, any>;
    data?: Record<string, any>;
}

export interface ThreadInterface {
    id: number;
    title: string;
    user: UserInterface;
    category: CategoryInterface;
    category_index_id?: number;
    created_at: string;
    first_post: PostInterface;
    posts_count: number;
    likes_count: number;
    posts: PostInterface[];
    ipfs?: string;
    unread?: number;
    no_recommend?: number;
    is_ban?: number;
    is_pin?:number;
    pin_user?:string;
    group?: GroupInterface;
    slug?: string;
    online?: boolean;
}

export interface TokenInterface {
    id: number;
    name: string;
    symbol: string;
    logo: string;
    contract_address: string;
    balance?: number;
};

export interface UploadedImageUrl {
    url: string;
    id: number;
    thumb_url: string;
}

export interface UserInterface {
    id: number;
    name: number;
    email: string;
    photo_url?: string;
    created_at?: string;
    updated_at?: string;
    groups?: { id: number, name: string, is_admin?: number }[];
    likes?: number;
    posts?: number;
    activate: number;
    refreshedToken?: string;
    api_version?: number;
    blocked_users?: number[];
    super_admin?: number;
    online?: boolean;
    is_follow?: number;
    is_admin?: number;
    joinStatus?: number;
}

export interface MemberListInterface {
    user_id: number;
    name: string;
    photo_url: string;
    created_at: string;
    likes_count: number;
    is_admin: number;
    is_ban: number;
    online?: boolean;
    last_login?:boolean;
    updated_at?:boolean;
    seven_days?:number;
}

export interface WithdrawRequest {
    id: number;
    group_id: 0;
    amount: string;
    order_id: string;
    status: number;
    to: string;
    token_id: number;
    user_id: number;
    wallet_id: number;
    transactionHash?: string;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
}