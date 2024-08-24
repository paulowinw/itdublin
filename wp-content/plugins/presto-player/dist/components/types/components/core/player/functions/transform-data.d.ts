import { PrestoConfig } from '../../../../interfaces';
export default function ({ preset, chapters, branding, src, analytics, automations, autoplay, blockAttributes, provider, provider_video_id, youtube, type, isAdmin, i18n, storage, }: PrestoConfig): {
    invertTime: boolean;
    storage: {
        enabled?: boolean;
        key?: string;
    };
    keyboard: {
        focused: boolean;
        global: boolean;
    };
    resetOnEnd: boolean;
    vimeo: {
        byline: boolean;
        portrait: boolean;
        title: boolean;
        speed: boolean;
        transparent: boolean;
        customControls: boolean;
        premium: boolean;
        playsinline: boolean;
    };
    youtube: {
        rel: number;
        showinfo: number;
        iv_load_policy: number;
        modestbranding: number;
        customControls: boolean;
        noCookie: boolean;
        playsinline: boolean;
    };
    tooltips: {
        controls: boolean;
        seek: boolean;
    };
    i18n: import("../../../../interfaces").i18nConfig;
    poster?: string;
    provider_video_id?: string;
    provider?: string;
    id: number;
    title: string;
    blockAttributes: {
        type: string;
        color?: string;
        id?: number;
        title?: string;
        src?: string;
        poster?: string;
        playsInline?: boolean;
        autoplay?: boolean;
        mutedPreview?: {
            enabled?: boolean;
            captions?: boolean;
        };
        mutedOverlay?: import("../../../../interfaces").MutedOverlay;
        constructor: Function;
        toString(): string;
        toLocaleString(): string;
        valueOf(): Object;
        hasOwnProperty(v: PropertyKey): boolean;
        isPrototypeOf(v: Object): boolean;
        propertyIsEnumerable(v: PropertyKey): boolean;
    };
    src: string;
    ajaxProgress: boolean;
    analytics: boolean;
    preset: import("../../../../interfaces").presetAttributes;
    automations: boolean;
    mutedPreview: {
        enabled: boolean;
    };
    loop: {
        active: boolean;
    };
    chapters: import("../../../../interfaces").prestoChapters;
    controls: string[];
    settings: string[];
    hideControls: boolean;
    captions: {
        active: boolean;
        language: string;
        update: boolean;
    };
    logo: string;
    logo_width: number;
    hide_logo: boolean;
    lazy_load_youtube: boolean;
    save_player_position: boolean;
    sticky_scroll: boolean;
    play_video_viewport: boolean;
    autoplay: boolean;
};
