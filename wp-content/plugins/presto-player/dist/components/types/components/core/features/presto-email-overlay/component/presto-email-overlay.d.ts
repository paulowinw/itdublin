import { i18nConfig, presetAttributes } from '../../../../../interfaces';
/**
 * This component is needed to prevent re-rendering of
 * main component with currentTime changes.
 */
export declare class PrestoEmailOverlay {
    player: any;
    direction?: 'rtl';
    preset: presetAttributes;
    videoId: number;
    i18n: i18nConfig;
    provider: string;
    currentTime: number;
    duration: number;
    ended: boolean;
    componentWillLoad(): void;
    setEnded(): void;
    setCurrentTime(e: any): void;
    /**
     * Remove listeners if destroyed
     */
    disconnectedCallback(): void;
    /**
     * Maybe render
     * @returns JSX
     */
    render(): any;
}
