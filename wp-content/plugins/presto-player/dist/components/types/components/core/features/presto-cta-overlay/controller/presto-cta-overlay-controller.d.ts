import { EventEmitter } from '../../../../../stencil-public-runtime';
import { i18nConfig, CTA } from '../../../../../interfaces';
export declare class PrestoCtaOverlayController {
    ended: boolean;
    currentTime: number;
    duration: number;
    direction?: 'rtl';
    cta?: CTA;
    i18n: i18nConfig;
    provider: string;
    enabled: boolean;
    show: boolean;
    loading: boolean;
    error: string;
    skipped: boolean;
    percentagePassed: number;
    playVideo: EventEmitter<void>;
    pauseVideo: EventEmitter<boolean>;
    restartVideo: EventEmitter<void>;
    ctaStateChange: EventEmitter<boolean>;
    componentWillLoad(): void;
    /**
     * Handle with the player is ended
     * @param val
     * @returns
     */
    handleEnded(val: any): void;
    /**
     * Wait for duration to start before checking time
     * @returns void
     */
    handleDuration(): void;
    handlePercentagePassed(): void;
    /**
     * Watch current time and check if we should
     * pause the video.
     */
    handleEnabled(): void;
    /**
     * When current time changes, check to see if we should
     * enable the overlay
     * @returns void
     */
    handleTime(): void;
    /**
     * Set enabled/disabled based on time that has passed
     */
    checkTime(): void;
    /**
     * Skip email collection
     */
    skip(): void;
    /**
     * Handle rewatch click.
     */
    rewatch(): void;
    /**
     * Maybe render
     * @returns JSX
     */
    handleCtaStateChange(val: any): void;
    render(): any;
}
