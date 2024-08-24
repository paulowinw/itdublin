import type { Components, JSX } from "../types/components";

interface PrestoPlaylistItem extends Components.PrestoPlaylistItem, HTMLElement {}
export const PrestoPlaylistItem: {
    prototype: PrestoPlaylistItem;
    new (): PrestoPlaylistItem;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
