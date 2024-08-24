const { __ } = wp.i18n;

const { Flex, FlexBlock, FlexItem, Spinner, Button, TextControl } =
  wp.components;

import { history } from "@/router/context";
import DatePicker from "../components/DatePicker";
import VideoAverageWatchTime from "../components/VideoAverageWatchTime";
import VideoTimeline from "../components/VideoTimeline";
import VideoViews from "../components/VideoViews";
import Player from "../../blocks/shared/Player";
import { getProvider } from "../../blocks/util";

const { useEffect, useState } = wp.element;
const { apiFetch } = wp;

const Video = ({ route, startDate, endDate, setStartDate, setEndDate }) => {
  const [loading, setLoading] = useState(true);
  const [video, setVideo] = useState({});
  const [error, setError] = useState("");
  const [thisName, setThisName] = useState(null);
  const [editing, setEditing] = useState(false);

  const back = () => {
    const { search } = history.location;
    history.push(`${search}#/`);
  };

  const getVideo = async () => {
    setLoading(true);
    try {
      let video = await apiFetch({
        url: `${prestoPlayer?.root}${prestoPlayer?.prestoVersionString}videos/${route?.params?.id}`,
      });
      setVideo(video);
      setThisName(video?.title);
    } catch (e) {
      if (e.code === "rest_no_route") {
        setError("Video Not Found");
      }
    } finally {
      setLoading(false);
    }
  };

  const putVideo = async () => {
    console.log(`New Video title  ${thisName}`);
    setLoading(true);
    try {
      const data = {
        ...video,
        ...{ title: thisName },
      };
      let saved = await wp.apiFetch({
        method: "POST",
        url: wp.url.addQueryArgs(
          `${prestoPlayer.root}${prestoPlayer.prestoVersionString}videos/${video.id}`,
          { _method: "PUT" }
        ),
        data,
      });

      if (!saved) {
        throw genericError;
      }
      setEditing(false);
      setVideo(saved);
    } catch (e) {
      setError(e?.message ? e.message : genericError);
    } finally {
      setLoading(false);
    }
  };

  const cancelEditing = () => {
    setThisName(video?.title);
    setEditing(false);
  };

  const renderVideoEditableTitle = () => {
    if (loading) {
      return <Spinner />;
    } else if (editing) {
      return (
        <div className="presto-inline-edit presto-inline-edit--editing">
          <TextControl
            className="presto-inline-edit__input"
            type="text"
            value={thisName}
            onChange={(title) => setThisName(title)}
          />
          <Button
            className="presto-inline-edit__button"
            isPrimary
            onClick={putVideo}
          >
            {" "}
            Save{" "}
          </Button>
          <Button
            className="presto-inline-edit__button"
            isSecondary
            onClick={cancelEditing}
          >
            {" "}
            Cancel{" "}
          </Button>
        </div>
      );
    } else {
      return (
        <div className="presto-inline-edit">
          <h1 className="presto-dashboard__title presto-inline-edit__text">
            {video?.title}
          </h1>

          <button
            className="presto-inline-edit__edit"
            onClick={() => setEditing(true)}
          >
            <span className="dashicon dashicons dashicons-edit"></span>
          </button>
        </div>
      );
    }
  };

  useEffect(() => {
    getVideo();
  }, []);

  if (error) {
    return (
      <div className="presto-flow">
        <Flex>
          <FlexBlock>
            <h2>{error}</h2>
          </FlexBlock>
        </Flex>
      </div>
    );
  }

  return (
    <div className="presto-flow">
      <Flex>
        <FlexBlock>
          <Button isSecondary onClick={back}>
            &larr; {__("Back to Dashboard", "presto-player")}
          </Button>
        </FlexBlock>
      </Flex>
      <Flex wrap>
        <FlexBlock>{renderVideoEditableTitle()}</FlexBlock>
        <FlexItem>
          <DatePicker
            startDate={startDate}
            setStartDate={setStartDate}
            endDate={endDate}
            setEndDate={setEndDate}
          />
        </FlexItem>
      </Flex>

      <div className="presto-dashboard presto-flow">
        <div className="presto-dashboard__row">
          <div className="presto-dashboard__item is-large">
            <VideoViews
              video_id={route?.params?.id}
              startDate={startDate}
              endDate={endDate}
            />
          </div>
          <div className="presto-dashboard__item">
            {!!Object.keys(video || {}).length && (
              <Player
                src={video?.src}
                attributes={{
                  title: video.title,
                }}
                type={getProvider(video.src)}
                preset={{
                  "play-large": true,
                  play: true,
                  progress: true,
                  rewind: true,
                  "fast-forward": true,
                  "current-time": true,
                  background_color: "#8421cb",
                  volume: true,
                  mute: true,
                  i18n: window.prestoPlayer.i18n,
                }}
              />
            )}
          </div>
          <div className="presto-dashboard__item">
            <VideoAverageWatchTime
              video_id={route?.params?.id}
              startDate={startDate}
              endDate={endDate}
            />
          </div>
        </div>
        <div className="presto-dashboard__row">
          <div className="presto-dashboard__item is-large">
            <VideoTimeline
              video_id={route?.params?.id}
              startDate={startDate}
              endDate={endDate}
            />
          </div>
        </div>
      </div>
    </div>
  );
};

export default Video;
