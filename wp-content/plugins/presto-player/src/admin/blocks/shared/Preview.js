import { __ } from "@wordpress/i18n";
import { useState, useEffect } from "@wordpress/element";
import { Disabled } from "@wordpress/components";
import {
  PrestoCtaOverlayUi,
  PrestoEmailOverlayUi,
} from "@presto-player/components-react";
import { timeToSeconds } from "../util";
import Player from "./Player";

export default ({
  state,
  branding,
  menu,
  src,
  poster,
  currentTime,
  preload,
  overlays,
  isDisabled = true,
}) => {
  const [renderKey, setRenderKey] = useState(1);
  const { email_collection, cta, search } = state;

  useEffect(() => {
    setRenderKey(renderKey + 1);
  }, [
    state.skin,
    state["play-large"],
    state.rewind,
    state.play,
    state["fast-forward"],
    state.progress,
    state["current-time"],
    state.mute,
    state.volume,
    state.speed,
    state.pip,
    state.fullscreen,
    state.captions,
    state.hide_logo,
  ]);

  const previews = {
    email: !!email_collection?.enabled && (
      <PrestoEmailOverlayUi
        className="email-overlay"
        slot="player-end"
        headline={email_collection?.headline}
        style={{
          "--presto-player-email-border-radiuss": `${email_collection?.button_radius}px`,
          "--plyr-color-main": state?.background_color || branding?.color,
          ...(email_collection.button_color
            ? {
                "--presto-player-button-color": `${email_collection.button_color}`,
              }
            : {}),
          ...(email_collection.button_text_color
            ? {
                "--presto-player-button-text": `${email_collection.button_text_color}`,
              }
            : {}),
        }}
        defaultHeadline={__("Enter your email to play.", "presto-player")}
        bottomText={email_collection?.bottom_text}
        allowSkip={email_collection?.allow_skip}
        skipText={__("Skip", "presto-player")}
        buttonText={email_collection?.button_text}
        placeholder={__("Email address", "presto-player")}
        i18n={window.prestoPlayer.i18n}
      />
    ),
    cta: !!cta?.enabled && (
      <PrestoCtaOverlayUi
        className="cta-overlay"
        style={{
          "--presto-player-button-border-radius": `${cta.button_radius}px`,
          ...(cta?.background_opacity
            ? {
                "--presto-player-cta-background-opacity": `${
                  cta?.background_opacity / 100
                }`,
              }
            : {}),
          ...(cta.button_color
            ? {
                "--presto-player-button-color": `${cta.button_color}`,
              }
            : {}),
          ...(cta.button_text_color
            ? {
                "--presto-player-button-text": `${cta.button_text_color}`,
              }
            : {}),
        }}
        slot="player-end"
        headline={cta?.headline}
        bottom-text={cta?.bottom_text}
        button-link={cta?.button_link}
        allow-skip={cta?.percentage !== 100 && cta?.show_skip}
        borderRadius={cta?.border_radius}
        allow-rewatch={cta?.percentage === 100 && cta?.show_rewatch}
        skip-text={__("Skip", "presto-player")}
        show-button={cta?.show_button}
        button-text={cta?.button_text}
        i18n={window?.prestoPlayer?.i18n}
      />
    ),
  };

  return (
    <Disabled
      isDisabled={isDisabled}
      className="disable-player"
      style={{ width: "100%" }}
    >
      <div className="presto-player__wrapper">
        <Player
          src={src || ""}
          className={`
            ${menu === "cta" && !!cta?.enabled && "cta-active"} 
            ${menu === "email" && !!email_collection?.enabled && "email-active"}
            ${menu === "search" && !!search?.enabled && "search-active"}`}
          preset={state}
          branding={{
            ...branding,
            logo: state?.hide_logo ? "" : branding["logo"],
          }}
          i18n={prestoPlayerAdmin?.i18n}
          preload={preload}
          attributes={{
            chapters: [],
            poster: poster,
          }}
          currentTime={currentTime ? timeToSeconds(currentTime) : ""}
          overlays={overlays}
          key={renderKey}
          adminPreview={previews?.[menu] || ""}
        />
      </div>
    </Disabled>
  );
};
