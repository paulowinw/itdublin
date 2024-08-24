/** @jsx jsx */
/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const {
  TextControl,
  BaseControl,
  Icon,
  Notice,
  Button,
  Flex,
  FlexItem,
  SelectControl,
  FlexBlock,
  Modal,
} = wp.components;
const { useState, useEffect } = wp.element;
const { useSelect, dispatch } = wp.data;

import { snackbarNotice } from "@/admin/blocks/util";
import Menu from "@/admin/ui/Menu";
import { css, jsx } from "@emotion/core";
import Preview from "./Preview";
import Behavior from "./Behavior";
import Controls from "./Controls";
import Style from "./Style";
import ActionBar from "../presets/ActionBar";
import CTA from "../presets/CTA";
import Email from "../presets/Email";

function EditPlayerPreset({
  type = "new",
  closeModal,
  addPreset,
  onSave,
  updatePreset,
  name = "",
  attributes,
  preset = {
    "play-large": true,
    rewind: true,
    play: true,
    "fast-forward": true,
    progress: true,
    "current-time": true,
    mute: true,
    volume: true,
    speed: false,
    pip: false,
    // behavior
    save_player_position: false,
    reset_on_end: false,
    sticky_scroll: false,
    // style
    hide_logo: false,
    border_radius: 0,
    skin: "default",
    background_color: "",
    control_color: "",

    // features
    cta: {},
    email_collection: {},
    action_bar: {},
  },
}) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [menu, setMenu] = useState("");
  const [thisName, setThisName] = useState(name);
  const [state, setState] = useState(preset);
  const branding = useSelect((select) => {
    return select("presto-player/player").branding();
  });

  const [value, setValue] = useState("");

  const genericError = {
    message: __(
      "The preset could not be saved. Please reload the page and try again.",
      "presto-player"
    ),
  };

  //you tube feature
  const youtube = useSelect((select) => {
    return select("presto-player/player").youtube();
  });

  useEffect(() => {
    setValue(youtube.channel_id);
  }, [youtube?.channel_id]);

  // update state
  const updateState = (updated = {}) => {
    setState({ ...state, ...updated });
  };

  const putPreset = async () => {
    setLoading(true);
    try {
      const data = {
        ...state,
        ...{ name: thisName },
      };
      let saved = await wp.apiFetch({
        method: "POST",
        url: wp.url.addQueryArgs(
          `${prestoPlayer.root}${prestoPlayer.prestoVersionString}audio-preset/${preset.id}`,
          { _method: "PUT" }
        ),
        data,
      });

      if (!saved) {
        throw genericError;
      }

      // update or create here
      updatePreset(saved);
      closeModal();
      !!onSave && onSave(saved);
      snackbarNotice({ message: __("Preset updated!", "presto-player") });
    } catch (e) {
      setError(e?.message ? e.message : genericError);
    } finally {
      setLoading(false);
    }

    // youtube id save
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
      console.log(e);
    }
  };

  const createPreset = async () => {
    setLoading(true);
    try {
      let saved = await wp.apiFetch({
        method: "POST",
        url:
          prestoPlayer.root + prestoPlayer.prestoVersionString + "audio-preset",
        data: {
          ...{ name: thisName },
          ...state,
        },
      });
      if (!saved) {
        throw genericError;
      }

      // update or create here
      addPreset(saved);
      closeModal();
      !!onSave && onSave(saved);
      snackbarNotice({ message: __("Preset created!", "presto-player") });
    } catch (e) {
      setError(e?.message ? e.message : genericError);
    } finally {
      setLoading(false);
    }
  };

  // validate and save
  const save = () => {
    if (!thisName) {
      setError(__("You must enter a name for the preset.", "presto-player"));
      return;
    }
    return type === "edit" ? putPreset() : createPreset();
  };

  const tabs = [
    {
      name: "controls",
      title: __("Controls", "presto-player"),
      icon: <Icon icon="admin-settings" />,
      component: <Controls updateState={updateState} state={state} />,
    },
    {
      name: "behavior",
      title: __("Behavior", "presto-player"),
      icon: <Icon icon="admin-generic" />,
      component: <Behavior updateState={updateState} state={state} />,
    },
    {
      name: "style",
      title: __("Style", "presto-player"),
      icon: <Icon icon="admin-customizer" />,
      component: <Style updateState={updateState} state={state} />,
    },
    {
      name: "email",
      title: __("Email Capture", "presto-player"),
      icon: <Icon icon="email" />,
      component: <Email updateState={updateState} state={state} />,
    },
    {
      name: "cta",
      title: __("Call To Action", "presto-player"),
      icon: <Icon icon="megaphone" />,
      component: <CTA updateState={updateState} state={state} />,
    },
    {
      name: "action_bar",
      title: __("Action Bar", "presto-player"),
      icon: <Icon icon="cover-image" />,
      component: (
        <ActionBar
          updateState={updateState}
          state={state}
          value={value}
          setValue={setValue}
        />
      ),
    },
  ];

  return (
    <Modal
      title={
        type == "edit"
          ? __("Edit A Audio Preset", "presto-player")
          : __("Create A New Audio Preset", "presto-player")
      }
      onRequestClose={closeModal}
      className="presto-player__modal-presets"
      overlayClassName="presto-player__modal-presets-overlay"
    >
      <div className="presto-player__preset-options" data-cy="preset-modal">
        <TextControl
          value={thisName}
          hideLabelFromVision={true}
          label={__("Preset Name", "presto-player")}
          onChange={(name) => setThisName(name)}
          placeholder={__("Enter a preset name...", "presto-player")}
          className="presto-player__modal--style-name"
          autoFocus
        />

        <Flex align="stretch" className="presto-player__style-preview-area">
          <FlexItem className="presto-player__style-sidebar">
            <div>
              <Menu
                items={tabs}
                title={__("Customize", "presto-player")}
                onSelect={setMenu}
              >
                {(item) => item.component}
              </Menu>
            </div>
          </FlexItem>
          <FlexBlock className="presto-player__style-preview-panel">
            <div
              style={{ position: "absolute", top: 0, left: 0, padding: "20px" }}
            >
              <SelectControl
                label={__("Skin", "presto-player")}
                labelPosition="side"
                value={state?.skin}
                hideLabelFromVision={true}
                options={[
                  { label: __("Basic", "presto-player"), value: "default" },
                  { label: __("Stacked", "presto-player"), value: "stacked" },
                ]}
                onChange={(skin) => {
                  updateState({ skin });
                }}
              />
            </div>
            {/*
        Disable the audio tag so the user clicking on it won't play the
        audio when the controls are enabled.
				*/}

            <Preview
              poster={attributes.poster}
              src={attributes.src}
              state={state}
              branding={branding}
              menu={menu}
              mediaTitle={attributes.title}
            />
          </FlexBlock>
        </Flex>

        <br />

        {error && (
          <BaseControl>
            <Notice
              className="presto-player__modal--error-notice"
              status="error"
              isDismissible={false}
              style={{ margin: 0 }}
            >
              {error.replace(/(<([^>]+)>)/gi, "")}
            </Notice>
          </BaseControl>
        )}
        <div
          css={css`
            display: flex;
            align-items: center;
            justify-content: space-between;
          `}
        >
          <div
            css={css`
              opacity: 0.5;
              font-size: 12px;
            `}
          >
            Preset ID: {preset.id}
          </div>
          <div>
            <Button isTertiary onClick={closeModal} style={{ margin: "0 6px" }}>
              {__("Cancel", "presto-player")}
            </Button>
            <Button
              isPrimary
              isBusy={loading}
              disabled={loading}
              onClick={save}
              data-cy="submit-preset"
            >
              {type == "edit"
                ? __("Update Preset", "presto-player")
                : __("Create Preset", "presto-player")}
            </Button>
          </div>
        </div>
      </div>
    </Modal>
  );
}
export default EditPlayerPreset;
