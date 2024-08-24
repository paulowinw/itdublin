export declare class PrestoBunny {
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
    thumbnail: string;
    previewUrl: string;
    videoAttributes: object;
    componentWillLoad(): void;
    render(): any;
}
