import { EventEmitter } from '../../../../stencil-public-runtime';
import { presetAttributes } from '../../../../interfaces';
export declare class PrestoAudio {
    private el;
    getRef?: (elm?: HTMLAudioElement) => void;
    autoplay: boolean;
    src: string;
    preload: string;
    poster: string;
    player: any;
    preset: presetAttributes;
    tracks: {
        label: string;
        src: string;
        srcLang: string;
    }[];
    provider: string;
    mediaTitle: string;
    audioAttributes: object;
    playVideo: EventEmitter<void>;
    pauseVideo: EventEmitter<true>;
    width: number;
    renderPosterImage(): any;
    hasPosterArea(): boolean;
    renderMobilePoster(): any;
    renderLargePlay(className?: string): any;
    componentDidLoad(): void;
    render(): any;
}
