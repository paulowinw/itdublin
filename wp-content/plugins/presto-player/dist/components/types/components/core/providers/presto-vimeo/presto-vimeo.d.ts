export declare class PrestoVimeo {
    src: string;
    poster: string;
    player: any;
    getRef?: (elm?: HTMLIFrameElement) => void;
    getId(url: any): string;
    parseHash(url: any): any;
    render(): any;
}
