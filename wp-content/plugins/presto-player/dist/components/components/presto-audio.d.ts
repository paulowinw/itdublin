import type { Components, JSX } from "../types/components";

interface PrestoAudio extends Components.PrestoAudio, HTMLElement {}
export const PrestoAudio: {
    prototype: PrestoAudio;
    new (): PrestoAudio;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
