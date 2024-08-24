const { __ } = wp.i18n;
const { useEffect } = wp.element;
const { dispatch } = wp.data;

import "./popup/stream/store/store";
import StreamPopup from "./popup/stream/Popup";

export default ({ closePopup, onSelect, isPrivate }) => {
  const onChoose = (video) => {
    video.url = video.playlistURL;
    video.thumbnail = video.thumbnailURL;
    video.preview = video.webPURL;
    onSelect(video);
  };

  // set privacy option in store
  useEffect(() => {
    dispatch("presto-player/bunny-popup").setIsPrivate(isPrivate);
  }, [isPrivate]);

  // render template
  return (
    <StreamPopup
      onClose={closePopup}
      onChoose={onChoose}
      header={
        isPrivate
          ? __("Private Stream Library", "presto-player")
          : __("Public Stream Library", "presto-player")
      }
      title={
        isPrivate
          ? __("Private Video Stream", "presto-player")
          : __("Public Video Stream", "presto-player")
      }
    />
  );
};
