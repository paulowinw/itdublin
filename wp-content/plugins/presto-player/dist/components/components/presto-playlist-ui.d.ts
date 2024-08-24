import type { Components, JSX } from "../types/components";

interface PrestoPlaylistUi extends Components.PrestoPlaylistUi, HTMLElement {}
export const PrestoPlaylistUi: {
    prototype: PrestoPlaylistUi;
    new (): PrestoPlaylistUi;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
