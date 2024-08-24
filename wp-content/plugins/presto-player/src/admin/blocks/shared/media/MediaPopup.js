/** @jsx jsx */
/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const {
  Modal,
  Spinner,
  Button,
  BaseControl,
  Flex,
  FlexBlock,
  Notice,
  Card,
  CardBody,
  Disabled,
  DropZone,
  FormFileUpload,
  DropZoneProvider,
} = wp.components;
const { useEffect, useState, useRef } = wp.element;
import MediaItem from "./MediaItem";
import MediaFolder from "./MediaFolder";

import { css, jsx } from "@emotion/core";

export default ({
  onClose,
  title,
  header,
  onLoad,
  items,
  folders,
  fetching,
  progressMessage,
  onSelect,
  error,
  onDelete,
  onUpload,
  progress,
}) => {
  const [selected, setSelected] = useState({});
  const [deleteConfirm, setDeleteConfirm] = useState(false);
  const buttonRef = useRef();

  useEffect(() => {
    onLoad && onLoad();
  }, []);

  const bytesToSize = (bytes) => {
    var sizes = ["Bytes", "KB", "MB", "GB", "TB"];
    if (bytes == 0) return "0 Byte";
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + " " + sizes[i];
  };

  const toDate = (d) => {
    d = new Date(d);
    var hours = d.getHours();
    var minutes = d.getMinutes();
    var ampm = hours >= 12 ? "pm" : "am";
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? "0" + minutes : minutes;

    return (
      d.getDate() +
      "-" +
      (d.getMonth() + 1) +
      "-" +
      d.getFullYear() +
      " at " +
      hours +
      ":" +
      minutes +
      ampm
    );
  };

  const deleteSelected = () => {
    onDelete(selected);
    setDeleteConfirm(false);
  };

  const isSelected = () => {
    return Object.keys(selected || {}).length;
  };

  const sidebarContent = () => {
    if (!isSelected()) {
      return <></>;
    }
    return (
      <div className="presto-player__media-modal-sidebar-content">
        <BaseControl>
          <Disabled key={selected.id}>
            {selected?.thumbnail ? (
              <img src={selected?.thumbnail} style={{ maxWidth: "100%" }} />
            ) : (
              <video preload="metadata">
                <source src={selected.previewUrl} />
              </video>
            )}
          </Disabled>
        </BaseControl>
        <BaseControl>
          <BaseControl.VisualLabel>
            {__("Name", "presto-player")}
          </BaseControl.VisualLabel>
          <h3 style={{ marginTop: "5px" }}>{selected.title}</h3>
        </BaseControl>

        {!!selected?.visibility && (
          <BaseControl>
            <BaseControl.VisualLabel>
              {__("Visibility", "presto-player")}
            </BaseControl.VisualLabel>
            <h3 style={{ marginTop: "5px" }}>{selected.visibility}</h3>
          </BaseControl>
        )}

        <BaseControl>
          <BaseControl.VisualLabel>
            {__("Size", "presto-player")}
          </BaseControl.VisualLabel>
          <h3 style={{ marginTop: "5px" }}>
            {bytesToSize(selected?.size || 0)}
          </h3>
        </BaseControl>

        <BaseControl>
          <BaseControl.VisualLabel>
            {__("Created", "presto-player")}
          </BaseControl.VisualLabel>
          <h3 style={{ marginTop: "5px" }}>{toDate(selected?.created_at)}</h3>
        </BaseControl>

        <BaseControl>
          {deleteConfirm ? (
            <Card>
              <CardBody>
                <p>
                  <strong>Are you sure?</strong>
                </p>
                <p>Are you sure you want to delete this video?</p>
                <Button isDestructive onClick={deleteSelected}>
                  Yes
                </Button>
                <Button onClick={() => setDeleteConfirm(false)}>Cancel</Button>
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
    );
  };

  const selectVideo = () => {
    if (selected) {
      onSelect(selected);
      onClose();
    }
  };

  const itemsContent = () => {
    if (fetching) {
      return (
        <Flex className="presto-player__media-loading">
          <FlexBlock style={{ textAlign: "center" }}>
            {progress ? (
              <>
                <div>
                  <strong>
                    {progressMessage || __("Uploading", "presto-player")}
                  </strong>
                </div>
                <div>
                  {__(
                    "Please don't navigate away from this page.",
                    "presto-player"
                  )}
                </div>
                <progress
                  className="presto-progress"
                  max="100"
                  value={progress}
                  style={{ width: "100px" }}
                ></progress>
              </>
            ) : (
              <Spinner />
            )}
          </FlexBlock>
        </Flex>
      );
    }

    if (!items?.length) {
      return (
        <Flex align-items="center" className="presto-player__media-not-found">
          <div>
            <h2>Drop video files here to upload</h2>
            <p>or browse for a video</p>
            <FormFileUpload
              isSecondary
              accept="video/mp4,video/x-m4v,video/*"
              onChange={(e) => {
                if (!e.target.files) {
                  return;
                }
                onUpload(e.target.files);
                jQuery(e.target).val(null);
              }}
            >
              {__("Upload New Video", "presto-player")}
            </FormFileUpload>
          </div>
        </Flex>
      );
    }

    return (
      <div className="presto-player__media-list">
        <h2>{title}</h2>

        {folders && (
          <div className="presto-player__media-list-folders">
            {folders.map((folder) => {
              return <MediaFolder key={folder.id} item={folder} />;
            })}
          </div>
        )}

        <div className="presto-player__media-list-items">
          {items.map((item) => {
            return (
              <MediaItem
                item={item}
                key={item.id}
                onClick={() => {
                  if (selected === item) {
                    setSelected({});
                  } else {
                    setSelected(item);
                  }
                }}
                className={selected === item ? "is-selected" : ""}
              />
            );
          })}
        </div>
      </div>
    );
  };

  return (
    <Modal
      isFullScreen
      title={header ? header : __("Add Media", "presto-player")}
      onRequestClose={onClose}
      className="presto-player__media-modal presto-player__full-modal"
      overlayClassName="presto-player__modal-overlay"
    >
      <div className="presto-player__media-modal-layout" data-cy="media-modal">
        <div className="presto-player__media-modal-header">
          <div
            className="presto-player__media-modal-upload"
            css={css`
              display: flex;
              align-items: center;
            `}
          >
            <FormFileUpload
              isPrimary
              accept="video/mp4,video/x-m4v,video/*"
              onChange={(e) => {
                if (!e.target.files) {
                  return;
                }
                onUpload(e.target.files);
                jQuery(e.target).val(null);
              }}
            >
              {__("Upload New Video", "presto-player")}
            </FormFileUpload>
            <div
              css={css`
                margin-left: 10px;
              `}
            >
              {__("Or drag a file here to upload.", "presto-player")}
            </div>
          </div>
          {error && (
            <Notice status="error" isDismissible={false}>
              {error}
            </Notice>
          )}
        </div>
        <div className="presto-player__media-modal-content">
          <DropZoneProvider>
            {itemsContent()}
            <DropZone label={"Drop files"} onFilesDrop={onUpload} />
          </DropZoneProvider>
        </div>
        <div className="presto-player__media-modal-sidebar">
          {sidebarContent()}
        </div>
        <div className="presto-player__media-modal-footer">
          <Button
            isPrimary
            disabled={!isSelected()}
            onClick={selectVideo}
            ref={buttonRef}
          >
            {__("Choose", "presto-player")}
          </Button>
        </div>
      </div>
    </Modal>
  );
};
