/** @jsx jsx */
/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { TextControl, ExternalLink, Button, Flex } = wp.components;
const { useState, useEffect } = wp.element;
const { useSelect, dispatch } = wp.data;

import { css, jsx } from "@emotion/core";

export default ({ onClose, value, setValue }) => {
  const [saving, setSaving] = useState(false);

  const userCanReadSettings = wp.data.useSelect((select) =>
    select("core").canUser("read", "settings")
  );

  const youtube = useSelect((select) => {
    return select("presto-player/player").youtube();
  });

  useEffect(() => {
    setValue(youtube.channel_id);
  }, [youtube?.channel_id]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    dispatch("presto-player/player").updateYoutube({ channel_id: value });

    const data = {
      ...youtube,
      ...{ channel_id: value },
    };

    try {
      let response = await wp.apiFetch({
        path: "wp/v2/settings",
        method: "POST",
        data: {
          presto_player_youtube: data,
        },
      });
      if (response?.presto_player_youtube) {
        dispatch("presto-player/player").setYoutube(
          response?.presto_player_youtube
        );
        onClose();
      }
    } catch (e) {
    } finally {
      setSaving(false);
    }
  };

  // use must be able to read settings
  if (!userCanReadSettings) {
    return "";
  }

  return (
    <form onSubmit={handleSubmit}>
      <TextControl
        css={css`
          margin-bottom: 0 !important;
        `}
        label={__("Youtube Channel ID", "presto-player")}
        help={
          <p>
            <ExternalLink href="https://support.google.com/youtube/answer/3250431?hl=en">
              {__("Find my channel id", "presto-player")}
            </ExternalLink>
          </p>
        }
        value={value}
        onChange={(channel_id) => setValue(channel_id)}
        // required
      />

      <Button
        disabled={saving}
        isBusy={saving}
        css={css`
          margin-bottom: 1em;
        `}
        isPrimary
        type="submot"
      >
        {__("Save", "presto-player")}
      </Button>
      <Button
        onClick={onClose}
        css={css`
          margin-bottom: 1em;
        `}
        isTertiary
      >
        {__("Cancel", "presto-player")}
      </Button>
    </form>
  );
};
