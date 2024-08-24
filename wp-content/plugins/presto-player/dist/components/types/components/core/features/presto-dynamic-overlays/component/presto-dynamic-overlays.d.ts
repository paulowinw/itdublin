import { EventEmitter } from '../../../../../stencil-public-runtime';
import { DynamicOverlay, presetAttributes } from '../../../../../interfaces';
export declare class PrestoDynamicOverlays {
    private topLeft;
    private topRight;
    private container;
    private watermarkRef;
    el: HTMLPrestoDynamicOverlaysElement;
    overlays: Array<DynamicOverlay>;
    player: any;
    preset: presetAttributes;
    enabled: boolean;
    currentTime: number;
    destroy: boolean;
    reloadComponent: EventEmitter<void>;
    private refs;
    componentDidLoad(): void;
    /**
     * Check validity of the overlays.
     * Blow up if any funny business.
     */
    checkValidity(): void;
    /**
     * Check if the component is valid.
     * If invalid, run a callback.
     *
     * @param component
     * @param text
     * @returns
     */
    checkComponent(component: any, text: any, callback: any): any;
    /**
     * Show the overlay
     * @param overlay
     * @returns
     */
    shouldShowOverlay(overlay: any): boolean;
    /**
     * Render the watermark
     */
    renderOverlay(overlay: any): any;
    /**
     * Should we show the watermark?
     */
    shouldShowWatermark(position: any): boolean;
    render(): any;
}
