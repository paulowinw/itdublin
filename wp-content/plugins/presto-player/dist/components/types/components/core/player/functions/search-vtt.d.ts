/**
 *
 * @param {TextTrack} track
 * @returns Cues fron the track
 */
export declare const vttToJson: (track: any) => {
    cues: any;
};
/**
 *
 * @param {any} player
 * @param {string} searchString
 * @returns Array of time in seconds where the search string is found.
 */
export declare function searchVtt(player: any, searchString: any): any;
export declare function removeOldMarkers(player: any): void;
export declare function setMarkers(player: any, points: any): void;
