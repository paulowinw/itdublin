export declare class PrestoVideo {
    getRef?: (elm?: HTMLVideoElement) => void;
    autoplay: boolean;
    src: string;
    preload: string;
    poster: string;
    player: any;
    tracks: {
        label: string;
        src: string;
        srcLang: string;
    }[];
    playsinline: boolean;
    provider: string;
    videoAttributes: object;
    render(): any;
}
