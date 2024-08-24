import { EventEmitter } from '../../../../../stencil-public-runtime';
import { ActionBarConfig, YoutubeConfig } from '../../../../../interfaces';
export declare class PrestoActionBar {
    el: HTMLElement;
    ended: boolean;
    config: ActionBarConfig;
    currentTime: number;
    duration: number;
    direction?: 'rtl';
    youtube?: YoutubeConfig;
    show: boolean;
    youtubeRenderKey: number;
    percentagePassed: number;
    actionBarStateChange: EventEmitter<boolean>;
    componentWillLoad(): void;
    /**
     * Wait for duration to start before checking time
     * @returns void
     */
    handleDuration(): void;
    /**
     * Handle with the player is ended
     * @param val
     * @returns
     */
    handleEnded(val: any): void;
    handlePercentagePassed(): void;
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
    handleButtonCountChange(newVal: any, oldVal: any): void;
    youtubeButton(): any;
    customButton(): any;
    handleCtaStateChange(val: any): void;
    render(): any;
}
