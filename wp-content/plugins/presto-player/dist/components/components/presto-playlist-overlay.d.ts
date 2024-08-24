import type { Components, JSX } from "../types/components";

interface PrestoPlaylistOverlay extends Components.PrestoPlaylistOverlay, HTMLElement {}
export const PrestoPlaylistOverlay: {
    prototype: PrestoPlaylistOverlay;
    new (): PrestoPlaylistOverlay;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
