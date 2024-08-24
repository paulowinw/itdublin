/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Button } = wp.components;
const { useState, useEffect } = wp.element;
const { useSelect } = wp.data;

export default ({ onChoose }) => {
  const [video, setVideo] = useState(null);
  const [canSelect, setCanSelect] = useState(false);

  const selectedId = useSelect((select) =>
    select("presto-player/bunny-popup").ui("selectedId")
  );
  const videos = useSelect((select) =>
    select("presto-player/bunny-popup").videos()
  );

  // update selected video when videos or selected id changes
  useEffect(() => {
    setVideo(
      selectedId ? videos.find((video) => video.guid === selectedId) : null
    );
  }, [videos, selectedId]);

  // set if we can select if video has available resolutions
  useEffect(() => {
    if (video?.status == 3 && video?.availableResolutions.length) {
      setCanSelect(true);
      return;
    }
    setCanSelect(video?.status > 3 && video?.status < 5);
  }, [video?.availableResolutions]);

  return (
    <Button isPrimary disabled={!canSelect} onClick={() => onChoose(video)}>
      {video?.id && !canSelect
        ? __("Please wait, video is encoding...", "presto-player")
        : __("Choose", "presto-player")}
    </Button>
  );
};
