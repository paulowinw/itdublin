/**
 * Find out if time is passed.
 * @returns boolean
 */
export declare function timePassed({ current, duration, showAfter }: {
    current: number;
    duration: number;
    showAfter: number;
}): boolean;
export declare function lightOrDark(color: string): "light" | "dark";
export declare function timeToSeconds(time: any): number;
export declare function getMobileOperatingSystem(): "Windows Phone" | "Android" | "iOS" | "unknown";
export declare function isIOS(): boolean;
export declare function isMobile(): boolean;
/**
 * Is iOS Youtube Fullscreen.
 */
export declare function isiOSYoutubeFullscreen(player: any): boolean;
export declare function isWebView(): boolean;
export declare function isAndroidWebView(): boolean;
export declare function parseColor(color: any): any[];
export declare function exitFullScreen(player: any): void;
