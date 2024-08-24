import { EventEmitter } from '../../../../stencil-public-runtime';
import { MutedOverlay, presetAttributes } from '../../../../interfaces';
export declare class PrestoMutedOverlay {
    mutedPreview: boolean;
    mutedOverlay: MutedOverlay;
    preset: presetAttributes;
    playVideo: EventEmitter<void>;
    render(): any;
}
