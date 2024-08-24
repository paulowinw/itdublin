/** @jsx jsx */
import { PrestoPlayer } from "@presto-player/components-react";
import { css, jsx } from "@emotion/core";
import React, { useEffect, useState } from "react";
import { getProvider } from "../util";

export default ({ id, src }) => {
  const [data, setData] = useState({});
  const [loading, setLoading] = useState(false);
  const [provider, setProvider] = useState("");
  const [renderKey, setRenderKey] = useState(1);

  useEffect(() => {
    setRenderKey(renderKey + 1);
  }, [id, src, data]);

  useEffect(() => {
    getHtml(id);
  }, [id]);

  useEffect(() => {
    if (src) {
      setProvider(getProvider(src));
    }
  }, [src]);

  const getHtml = async (id) => {
    if (!id) {
      return;
    }
    setLoading(true);
    try {
      const res = await fetch(`/wp-admin/admin-ajax.php`, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "Cache-Control": "no-cache",
        },
        body: new URLSearchParams({
          action: "presto_get_media_attributes",
          _wpnonce: window.et_fb_options.et_admin_load_nonce,
          id,
        }),
      }).then((response) => response.json());
      setData(res.data);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="et-fb-preloader et-fb-preloader__loading">
        <div className="et-fb-loader" />
      </div>
    );
  }

  if (data?.id) {
    src = src ? src : data.src;

    if (
      data?.provider === "bunny" &&
      (provider === "self-hosted" || provider === "bunny")
    ) {
      return (
        <presto-video-curtain-ui>
          <span>
            Bunny.net videos cannot be previewed in the DIVI editor due to DIVI
            iframe having no referrer.
          </span>
          <presto-player-button
            full
            type="primary"
            target="_blank"
            href="https://prestoplayer.com/docs/bunny-net-videos-not-showing-in-divi-editor"
          >
            Learn More
          </presto-player-button>
        </presto-video-curtain-ui>
      );
    }

    return (
      <div
        css={css`
          ${data?.styles}
        `}
      >
        <PrestoPlayer
          key={renderKey}
          video_id={data.id}
          preset={data.preset}
          src={src}
          chapters={data.chapters}
          tracks={data.tracks}
          branding={data.branding}
          blockAttributes={data.blockAttributes}
          poster={data?.blockAttributes?.poster}
          mediaTitle={data?.blockAttributes?.title}
          config={data.config}
          skin={data.skin}
          analytics={data.analytics}
          automations={data.automations}
          provider={provider || data?.provider}
          provider_video_id={data.provider_video_id}
          youtube={data.youtube}
        ></PrestoPlayer>
      </div>
    );
  } else {
    return "Choose Media Item";
  }
};
