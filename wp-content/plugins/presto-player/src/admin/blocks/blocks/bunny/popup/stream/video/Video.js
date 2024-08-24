/** @jsx jsx */
import { css, jsx } from "@emotion/core";
const { Icon, Button } = wp.components;
const { useEffect, useState } = wp.element;
const { useSelect, dispatch } = wp.data;
import ProgressOverlay from "../ProgressOverlay";
import ProgressBar from "../ProgressBar";
import Thumbnail from "../ThumbTemplate";

import {
  isSelectable,
  getStatusText,
  getLengthToTime,
  bytesToSize,
} from "../utils";

export default ({ video }) => {
  const [selected, setSelected] = useState();
  const isPrivate = useSelect((select) =>
    select("presto-player/bunny-popup").isPrivate()
  );
  const selectedId = useSelect((select) =>
    select("presto-player/bunny-popup").ui("selectedId")
  );

  useEffect(() => {
    setSelected(selectedId ? selectedId === video.guid : null);
  }, [selectedId]);

  /**
   * Status badge
   * @returns JSX
   */
  const renderStatusBadge = () => (
    <Button
      isSmall
      isPrimary
      isBusy={!isSelectable(video)}
      css={css`
        font-size: 11px;
        color: #ffffff;
        padding: 2px 10px;
        border-radius: 9999px;
      `}
    >
      {getStatusText(video)}
    </Button>
  );

  /**
   * Render thumbnail
   * @returns
   */
  const renderThumbnail = () => {
    if (video.status < 3) {
      return <ProgressOverlay progress={video.encodeProgress} />;
    }

    const url = isPrivate ? video?.thumbnailURLSigned : video?.thumbnailURL;

    if (url) {
      return (
        <img
          css={css`
            max-width: 100%;
            object-fit: cover;
            width: 100%;
            height: 140px;
            display: block;
          `}
          src={url}
        />
      );
    }
  };

  const renderLength = () => (
    <div
      css={css`
        display: flex;
        align-items: center;
      `}
    >
      <span
        css={css`
          margin-right: 10px;
          display: flex;
          align-items: center;
        `}
      >
        <Icon
          css={css`
            width: 14px;
            height: 14px;
            font-size: 14px;
            margin-right: 5px;
            opacity: 0.5;
          `}
          icon="clock"
          size={14}
        />
        <span>{getLengthToTime(video.length)}</span>
      </span>
      <span
        css={css`
          display: flex;
          align-items: center;
        `}
      >
        <Icon
          css={css`
            opacity: 0.5;
            width: 14px;
            height: 14px;
            font-size: 14px;
            margin-right: 5px;
          `}
          icon="database"
          size={14}
        />
        <span>{bytesToSize(video.storageSize)}</span>
      </span>
    </div>
  );

  return (
    <Thumbnail
      onClick={() => {
        dispatch("presto-player/bunny-popup").setUI("selectedId", video?.guid);
      }}
      css={css`
        ${selected &&
        "box-shadow: 0 0 0 0px #fff, 0 0 0 3px var(--wp-admin-theme-color, #007cba);"}
        border: 1px solid
          ${selected ? "var(--wp-admin-theme-color, #007cba)" : "#e0e0e0"};
      `}
      thumbnail={renderThumbnail()}
      badge={renderStatusBadge()}
      title={video.title}
      footer={renderLength()}
      after={
        video.status === 3 ? (
          <ProgressBar
            css={css`
              width: 100%;
              border-radius: 0px;
              margin: 0;
              background-color: #e3e3e3;
              height: 3px;
            `}
            progress={video.encodeProgress}
          />
        ) : (
          ""
        )
      }
    />
  );
};
