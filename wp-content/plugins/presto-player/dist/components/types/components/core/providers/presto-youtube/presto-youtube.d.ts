import { EventEmitter } from '../../../../stencil-public-runtime';
export declare class PrestoYoutube {
    src: string;
    poster: string;
    lazyLoad: boolean;
    player: any;
    getRef?: (elm?: HTMLIFrameElement | HTMLVideoElement | HTMLDivElement) => void;
    /**
     * Events
     */
    reload: EventEmitter<string>;
    /**
     * State
     */
    reloadPlayer: boolean;
    isWebView: boolean;
    /**
     * When player is set, do ratio and fixes
     * @returns
     */
    handlePlayerChange(): void;
    fixes(): void;
    getId(url: any): any;
    loadPlayer(): void;
    componentDidRender(): void;
    /**
     * detect if we're in a webview browser
     */
    setWebView(): void;
    loadImage(src: any, minWidth?: number): Promise<unknown>;
    setPoster(): void;
    componentWillLoad(): void;
    render(): any;
}
