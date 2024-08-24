/**
 * WordPress dependencies
 */

import { createHigherOrderComponent } from "@wordpress/compose";
import { useEffect, useState } from "@wordpress/element";
import { dispatch } from "@wordpress/data";
import { usePrevious } from "@/admin/blocks/util";
import { __ } from "@wordpress/i18n";
import apiFetch from "@/shared/services/fetch";

/**
 * Higher order component factory for injecting the editor colors as the
 * `colors` prop in the `withColors` HOC.
 *
 * @return {Function} The higher order component.
 */
export default () =>
  createHigherOrderComponent(
    (WrappedComponent) => (props) => {
      const {
        presets,
        attributes,
        setAttributes,
        defaultPreset,
        isSelected,
        branding,
      } = props;
      const [presetData, setPresetData] = useState({});
      const [count, setCount] = useState(1);
      let { poster, chapters } = attributes;

      // don't allow selection if there is an override
      useEffect(() => {
        if (isSelected && attributes?.selectionOverrideClientId) {
          dispatch("core/block-editor").selectBlock(
            attributes?.selectionOverrideClientId
          );
        }
      }, [isSelected]);

      // set preset data when presets are loaded
      useEffect(() => {
        if (presets && presets.length) {
          const thisPreset = presets.find((preset) => {
            return preset.id === attributes?.preset;
          });
          if (thisPreset) {
            setPresetData(thisPreset);
          } else {
            setPresetData(defaultPreset);
            setAttributes({ preset: defaultPreset?.id });
          }
        }
      }, [presets, attributes?.preset]);

      // re-render the player if presetdata, poster or chapters change
      useEffect(() => {
        onUpdate();
      }, [poster, presetData, chapters, branding.logo]);

      // increment update key
      const onUpdate = () => {
        setCount(count + 1);
      };

      // re-render only if times change
      const prevChapters = usePrevious(chapters);
      useEffect(() => {
        let times = chapters?.map((item) => item.time);
        let prevTimes = prevChapters?.map((item) => item.time);
        if (_.difference(times, prevTimes).length) {
          onUpdate();
        }
      }, [chapters]);

      const createVideo = async ({
        src,
        external_id,
        attachment_id,
        type,
        title,
      }) => {
        if (!src && !external_id && !attachment_id) {
          return;
        }
        const { id } = await apiFetch({
          method: "POST",
          path: "/presto-player/v1/videos",
          data: {
            attachment_id,
            post_id: wp.data.select("core/editor").getCurrentPostId(),
            external_id,
            ...(title ? { title } : {}),
            src,
            type,
          },
        });
        setAttributes({ id });
      };

      const lock = () => {
        return dispatch("core/editor").lockPostSaving("presto-player");
      };

      const unlock = () => {
        return dispatch("core/editor").unlockPostSaving("presto-player");
      };

      function onRemoveSrc() {
        let r = confirm(__("Remove this", "presto-player"));
        if (r) {
          setAttributes({
            src: "",
            id: undefined,
          });
        }
      }

      // make sure it's the default
      if (!attributes?.preset) {
        setAttributes({ preset: defaultPreset?.id });
      }

      return (
        <WrappedComponent
          {...props}
          lockSave={lock}
          unlockSave={unlock}
          createVideo={createVideo}
          onUpdate={onUpdate}
          onRemoveSrc={onRemoveSrc}
          presetData={presetData}
          setPresetData={setPresetData}
          renderKey={count}
        />
      );
    },
    "withPlayerEdit"
  );
