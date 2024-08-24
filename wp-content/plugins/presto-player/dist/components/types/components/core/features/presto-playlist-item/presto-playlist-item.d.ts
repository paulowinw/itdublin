import { EventEmitter } from '../../../../stencil-public-runtime';
export declare class PrestoPlaylistItem {
    /** Active status */
    active: boolean;
    /** Playing status */
    playing: boolean;
    /** Emit play event */
    triggerPlay: EventEmitter<void>;
    /** Emit pause event */
    triggerPause: EventEmitter<void>;
    /**
     * Rendering the component
     */
    render(): any;
}
