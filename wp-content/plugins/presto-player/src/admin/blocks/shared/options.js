const { useEffect, useState } = wp.element;
const { useSelect } = wp.data;
import { usePrevious } from "@/admin/blocks/util";

export default function ({ attributes, setAttributes }) {
  const { chapters, poster } = attributes;
  const [presetData, setPresetData] = useState({});
  const data = {
    branding: {},
    loading: false,
    presets: [],
    presetData: {},
  };

  data.branding = useSelect((select) => {
    return select("presto-player/player").branding();
  });
  useEffect(() => {
    document.body.style.setProperty("--plyr-color-main", data.branding?.color);
  }, [data.branding?.color]);

  data.loading = useSelect((select) => {
    return select("presto-player/player").presetsLoading();
  });
  data.presets = useSelect((select) => {
    return select("presto-player/player").getPresets();
  });

  // set preset data when presets are loaded
  useEffect(() => {
    const thisPreset = data.presets.find((preset) => {
      return preset.id === attributes?.preset;
    });
    data.presetData = thisPreset;
  }, [data.presets, attributes?.preset]);

  // This will reload the player to show the controls
  let [count, setCount] = useState(1);
  useEffect(() => {
    setCount(count + 1);
  }, [poster, data.presetData]);

  // re-render only if times change
  const prevChapters = usePrevious(chapters);
  useEffect(() => {
    let times = chapters?.map((item) => item.time);
    let prevTimes = prevChapters?.map((item) => item.time);
    if (_.difference(times, prevTimes).length) {
      setCount(count + 1);
    }
  }, [chapters]);

  return data;
}
