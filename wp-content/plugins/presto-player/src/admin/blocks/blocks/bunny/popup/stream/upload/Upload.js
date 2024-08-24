/** @jsx jsx */
import { css, jsx } from "@emotion/core";

const { Icon } = wp.components;
const { useState, useEffect, useContext } = wp.element;
const { useSelect, dispatch } = wp.data;
const { __ } = wp.i18n;

import chunkUpload from "@/admin/blocks/shared/media/chunk-upload";

import ProgressBar from "../ProgressBar";

export default ({ file, name, onComplete }) => {
  const [progress, setProgress] = useState(0);
  const [message, setMessage] = useState(__("Uploading", "presto-player"));
  const [error, setError] = useState("");
  const [created, setCreated] = useState(false);
  const type = useSelect((select) =>
    select("presto-player/bunny-popup").requestType()
  );
  const collection = useSelect((select) =>
    select("presto-player/bunny-popup").currentCollection()
  );

  let uploader;

  const upload = async () => {
    setMessage(__("Uploading", "presto-player"));
    uploader = chunkUpload({
      file: file?.[0] ? file?.[0] : file,
      path: `presto-player/v1/bunny/stream/upload`,
      onProgress: (percent) => {
        setProgress(percent); // leave 10% for storing
      },
      onComplete: createVideo,
      onError: (e) => {
        setError(e.message);
        setMessage(__("Error", "presto-player"));
        setProgress(0);
      },
    });
  };

  const createVideo = async ({ path, name }) => {
    setMessage(__("Creating", "presto-player"));
    try {
      const video = await wp.apiFetch({
        path: "presto-player/v1/bunny/stream/videos",
        method: "POST",
        data: {
          type,
          name,
          ...(collection?.guid ? { collection: collection.guid } : {}),
        },
      });
      setCreated(true);
      storeVideo({ path, video });
    } catch (e) {
      setError(e.message);
    } finally {
      setProgress(0);
    }
  };

  /**
   * Store the video on Bunny.net
   */
  const storeVideo = async ({ path, video }) => {
    await wp.apiFetch({
      path: "presto-player/v1/bunny/stream/store",
      method: "POST",
      data: {
        type,
        path,
        guid: video.guid,
      },
    });

    onComplete();
  };

  const onCancel = () => {
    uploader && uploader.cancel();
    dispatch("presto-player/bunny-popup").removeUpload(upload);
  };

  useEffect(() => {
    upload();
    return () => {
      uploader && uploader.cancel();
    };
  }, []);

  if (created) {
    return "";
  }

  return (
    <div
      css={css`
        display: flex;
        align-items: center;
        justify-content: space-between;
        animation: components-button__busy-animation 2500ms infinite linear;
        opacity: 1;
        padding: 6px 12px;
        border-radius: 99999px;
        border: 1px solid #dddddd;
        background-size: 100px 100%;
        background-image: linear-gradient(
          -45deg,
          #ffffff 33%,
          #f3f3f3 33%,
          #f3f3f3 70%,
          #ffffff 70%
        );
      `}
    >
      <div
        css={css`
          flex: 1;
          white-space: nowrap;
          overflow: hidden;
          max-width: 150px;
          text-overflow: ellipsis;
          font-weight: bold;
        `}
      >
        {!!error && error}
        {!!name && name} {file.name}...
      </div>
      <div
        css={css`
          display: flex;
          align-items: center;
        `}
      >
        <ProgressBar
          css={css`
            width: 50px;
            height: 3px;
            margin: 0 5px;
            background: #e3e3e3;
            border-radius: 9999px;
            overflow: hidden;
          `}
          progress={progress}
        />
        {/* <Icon onClick={onCancel} icon="no-alt" /> */}
      </div>
    </div>
  );
};
