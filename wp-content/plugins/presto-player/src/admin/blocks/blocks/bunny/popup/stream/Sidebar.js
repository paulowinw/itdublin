/** @jsx jsx */

const { __ } = wp.i18n;
const { Button, BaseControl, Disabled, Card, CardBody } = wp.components;
const { useState, useContext, useEffect } = wp.element;
const { dispatch, useSelect } = wp.data;

import {
  isSelectable,
  getStatusText,
  getLengthToTime,
  stampToDate,
  bytesToSize,
} from "./utils";

import { jsx, css } from "@emotion/core";

export default () => {
  const [deleteConfirm, setDeleteConfirm] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [video, setVideo] = useState(null);

  const isPrivate = useSelect((select) =>
    select("presto-player/bunny-popup").isPrivate()
  );
  const selectedId = useSelect((select) =>
    select("presto-player/bunny-popup").ui("selectedId")
  );
  const videos = useSelect((select) =>
    select("presto-player/bunny-popup").videos()
  );

  useEffect(() => {
    setVideo(
      selectedId ? videos.find((video) => video.guid === selectedId) : null
    );
  }, [videos, selectedId]);

  const onDelete = async () => {
    await wp.apiFetch({
      path: `presto-player/v1/bunny/stream/videos/${video.id}`,
      method: "DELETE",
      data: {
        type: isPrivate ? "private" : "public",
      },
    });
    dispatch("presto-player/bunny-popup").removeVideo(video);
    dispatch("presto-player/bunny-popup").setUI("selectedId", null);
    setDeleting(false);
    setDeleteConfirm(false);
  };

  const controlCSS = css`
    margin-top: 5px;
  `;

  if (!video) {
    return "";
  }

  const getThumbnail = (video) => {
    return isPrivate ? video?.webPURLSigned : video?.webPURL;
  };

  return (
    video && (
      <div className="presto-player__media-modal-sidebar-content">
        <BaseControl
          css={css`
            position: relative;
          `}
        >
          <Disabled key={video.id}>
            {isSelectable(video) && getThumbnail(video) && (
              <img src={getThumbnail(video)} css={{ maxWidth: "100%" }} />
            )}
          </Disabled>
          <Button
            isSmall
            isPrimary
            isBusy={!isSelectable(video)}
            css={css`
              ${isSelectable(video) ? "position: absolute;" : ""}
              top: 8px;
              right: 8px;
              font-size: 11px;
              color: #ffffff;
              padding: 2px 10px;
              border-radius: 9999px;
            `}
          >
            {getStatusText(video)}
          </Button>
        </BaseControl>
        <BaseControl>
          <BaseControl.VisualLabel>
            {__("Name", "presto-player")}
          </BaseControl.VisualLabel>
          <h3 css={controlCSS}>{video.title}</h3>
        </BaseControl>

        {!!video?.visibility && (
          <BaseControl>
            <BaseControl.VisualLabel>
              {__("Visibility", "presto-player")}
            </BaseControl.VisualLabel>
            <h3 css={controlCSS}>{video.visibility}</h3>
          </BaseControl>
        )}

        <BaseControl>
          <BaseControl.VisualLabel>
            {__("Size", "presto-player")}
          </BaseControl.VisualLabel>
          <h3 css={controlCSS}>{bytesToSize(video?.size || 0)}</h3>
        </BaseControl>

        <BaseControl>
          <BaseControl.VisualLabel>
            {__("Length", "presto-player")}
          </BaseControl.VisualLabel>
          <h3 css={controlCSS}>{getLengthToTime(video?.length)}</h3>
        </BaseControl>

        <BaseControl>
          <BaseControl.VisualLabel>
            {__("Created", "presto-player")}
          </BaseControl.VisualLabel>
          <h3 css={controlCSS}>{stampToDate(video?.created_at)}</h3>
        </BaseControl>

        <BaseControl>
          {deleteConfirm ? (
            <Card>
              <CardBody>
                <p>
                  <strong>{__("Are you sure?", "presto-player")}</strong>
                </p>
                <p>
                  {__(
                    "Are you sure you want to delete this video?",
                    "presto-player"
                  )}
                </p>
                <Button
                  isDestructive
                  disabled={deleting}
                  isBusy={deleting}
                  onClick={(e) => {
                    e.preventDefault();
                    onDelete();
                    setDeleting(true);
                  }}
                >
                  {__("Yes", "presto-player")}
                </Button>
                <Button onClick={() => setDeleteConfirm(false)}>
                  {__("Cancel", "presto-player")}
                </Button>
              </CardBody>
            </Card>
          ) : (
            <Button
              isDestructive
              onClick={() => {
                setDeleteConfirm(!deleteConfirm);
              }}
            >
              {__("Delete video", "presto-player")}
            </Button>
          )}
        </BaseControl>
      </div>
    )
  );
};
