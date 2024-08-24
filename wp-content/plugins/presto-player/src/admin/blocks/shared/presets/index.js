/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { withNotices, BaseControl, Spinner, Button } = wp.components;
const { useState, useEffect } = wp.element;
const { useSelect, dispatch } = wp.data;

import ProBadge from "@/admin/blocks/shared/components/ProBadge";
import EditPreset from "./Edit";
import Preset from "./Preset";
import styled from "@emotion/styled";

function PlayerPresets({ attributes, setAttributes }) {
  // modal
  const [modal, setModal] = useState(false);
  const openModal = (type) => setModal(type);
  const closeModal = () => setModal(false);
  const [presetData, setPresetData] = useState(null);
  const [name, setName] = useState(null);

  // preset data
  const { presets, loading } = useSelect((select) => {
    return {
      presets: select("presto-player/player").getPresets(),
      loading: select("presto-player/player").isResolving("getPresets"),
    };
  });

  // preset actions
  const addPreset = (preset) => {
    dispatch("presto-player/player").addPreset(preset);
  };
  const updatePreset = (preset) => {
    dispatch("presto-player/player").updatePreset(preset);
  };
  const removePreset = (preset) => {
    dispatch("presto-player/player").removePreset(preset);
  };

  // set this preset id
  const setPreset = (preset) => {
    setAttributes({ preset: preset.id });
  };

  const PresetWrap = styled.div`
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
  `;

  if (loading) {
    return (
      <div className="presto-settings__loading">
        <Spinner />
      </div>
    );
  }

  return (
    <>
      {presets.length ? (
        <p>
          {__(
            "Select a video configuration preset, or add your own.",
            "presto-player"
          )}
        </p>
      ) : (
        ""
      )}

      <BaseControl>
        <PresetWrap>
          {(presets || []).length
            ? presets.map((preset, i) => {
                return (
                  <Preset
                    setPreset={setPreset}
                    index={i}
                    isActive={attributes?.preset === preset.id}
                    preset={preset}
                    key={preset.id}
                    onEdit={() => {
                      setName(preset.name);
                      setPresetData(preset);
                      setModal("edit");
                    }}
                    remove={removePreset}
                  />
                );
              })
            : __(
                "No style presets. You can create a new style by clicking 'Add New Style'.",
                "presto-plugin"
              )}
        </PresetWrap>
      </BaseControl>
      <BaseControl>
        <Button
          isPrimary
          data-cy="add-new-preset"
          onClick={() => {
            if (!prestoPlayer?.isPremium) {
              dispatch("presto-player/player").setProModal(true);
              return;
            }
            openModal("new");
          }}
        >
          {__("Add New Preset", "presto-player")}
        </Button>
        {!prestoPlayer?.isPremium && <ProBadge />}
      </BaseControl>
      {modal == "new" && (
        <EditPreset
          closeModal={closeModal}
          addPreset={addPreset}
          type="new"
          onSave={setPreset}
        />
      )}
      {modal == "edit" && (
        <EditPreset
          closeModal={closeModal}
          addPreset={addPreset}
          updatePreset={updatePreset}
          type="edit"
          preset={presetData}
          name={name}
        />
      )}
    </>
  );
}

export default withNotices(PlayerPresets);
