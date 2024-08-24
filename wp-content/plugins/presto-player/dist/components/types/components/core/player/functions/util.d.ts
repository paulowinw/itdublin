export declare function getYoutubeId(url: any): any;
export declare function getVimeoId(url: any): string;
export declare function determineVideoUrlType(url: any): {
    video_id: any;
    type: string;
    $video_id?: undefined;
    $type?: undefined;
} | {
    $video_id: number;
    $type: string;
    video_id?: undefined;
    type?: undefined;
};
export declare function isHLS(url: any): boolean;
export declare function isNotEmptyObject(item: any): number;
export declare function isNotEmptyArray(item: any): boolean;
export declare function timePassed({ current, duration, showAfter }: {
    current: number;
    duration: number;
    showAfter: number;
}): boolean;
export declare function getParents(elem: any): any[];
export declare function setAttributes(element: any, attributes: any): void;
export declare function createElement(type: any, attributes: any, text: any): any;
export declare function toggleClass(element: any, className: any, force: any): any;
