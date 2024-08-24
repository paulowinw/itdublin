import type { Components, JSX } from "../types/components";

interface PrestoPlaylist extends Components.PrestoPlaylist, HTMLElement {}
export const PrestoPlaylist: {
    prototype: PrestoPlaylist;
    new (): PrestoPlaylist;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
