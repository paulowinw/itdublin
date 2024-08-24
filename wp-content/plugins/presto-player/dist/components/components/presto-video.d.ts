import type { Components, JSX } from "../types/components";

interface PrestoVideo extends Components.PrestoVideo, HTMLElement {}
export const PrestoVideo: {
    prototype: PrestoVideo;
    new (): PrestoVideo;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
