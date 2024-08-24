// External Dependencies
import createCache from "@emotion/cache";
import debounce from "debounce-promise";
import memoizeOne from "memoize-one";
import React, { Component } from "react";
import { NonceProvider } from "react-select";
import AsyncSelect from "react-select/async";

const { __ } = wp.i18n;

// Internal Dependencies
import "./style.css";

class PrestoNonceProvider extends NonceProvider {
  createEmotionCacheCustom = function (nonce) {
    return createCache({
      nonce,
      key: "custom-select-style",
      container: this.props.container,
    });
  };

  createEmotionCache = memoizeOne(this.createEmotionCacheCustom);
}

class VideoSelector extends Component {
  static slug = "prpl_video_selector";

  constructor(props) {
    super(props);
    this.state = { videos: [] };

    const wait = 500; // milliseconds
    const loadVideos = (inputValue) =>
      this.loadVideos({ searchTerm: inputValue });
    this.debouncedLoadVideos = debounce(loadVideos, wait, {
      leading: true,
    });
  }

  /**
   * Search for videos
   *
   * @param {string} input
   * @returns {array}
   */
  fetchVideos = (input = "") => {
    return fetch(prestoPlayer.ajaxurl, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "Cache-Control": "no-cache",
      },
      body: new URLSearchParams({
        action: "presto_fetch_videos",
        search: input,
        _wpnonce: prestoPlayer.nonce,
      }),
    })
      .then((response) => response.json())
      .then((response) => response.data || []);
  };

  /**
   * Fetch a specific video
   * @param {array} input
   * @returns {array}
   */
  fetchVideo = (input = "") => {
    if (!input) {
      return [];
    }

    return fetch(`/wp-admin/admin-ajax.php`, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "Cache-Control": "no-cache",
      },
      body: new URLSearchParams({
        action: "presto_fetch_videos",
        post_id: input,
      }),
    })
      .then((response) => response.json())
      .then((response) => response.data || []);
  };

  /**
   * Load the videos from the db
   * @param {string} input
   * @returns
   */
  loadVideos = async (input) => {
    input = {
      searchTerm: "",
      postId: "",
      ...input,
    };

    let [video, videos] = await Promise.all([
      this.fetchVideo(input.postId),
      this.fetchVideos(input.searchTerm),
    ]);

    if (video[0] && !videos.find((item) => item.ID === video[0].ID)) {
      videos.push(video[0]);
    }
    videos = videos.map((item) => {
      return {
        label: item.post_title || __("Untitled", "presto-player"),
        value: item.ID,
      };
    });

    this.setState({ videos });

    return videos;
  };

  /**
   * Handle input value change.
   *
   * @param {object} event
   */
  _onChange = (event) => {
    this.props._onChange(this.props.name, event.value);
  };

  /**
   * Handle edit video click.
   * @returns null
   */
  _onEditVideoClick = () => {
    var video_id = this.props.value;
    if (!video_id) {
      return;
    }
    var win = window.open(
      `/wp-admin/post.php?post=${video_id}&action=edit`,
      "_blank"
    );
    win.focus();
  };

  /**
   * Handle create video click.
   */
  _onCreateVideoClick = () => {
    var win = window.open(
      `/wp-admin/post-new.php?post_type=pp_video_block`,
      "_blank"
    );
    win.focus();
  };

  /**
   * Determine the video label
   * @returns {string}
   */
  currentVideoLabel = () => {
    if (!this.state.videos) {
      return "";
    }

    const video = (this.state.videos || []).find((video) => {
      return video.value === parseInt(this.props.value);
    });

    if (!video) {
      return "";
    }

    return video.label;
  };

  /**
   * Render the component
   * @returns {JSX}
   */
  render() {
    return (
      <div className="presto-player-divi-editor">
        <ul className="presto-player-divi-editor__inputs">
          <li>
            <PrestoNonceProvider container={window.parent.document.body}>
              <AsyncSelect
                id={`prpd_video_selector-${this.props.name}`}
                className="prpd_video_selector"
                classNamePrefix="prpd_video_select"
                cacheOptions
                defaultOptions
                name={this.props.name}
                value={{
                  value: this.props.value,
                  label: this.currentVideoLabel(),
                }}
                onChange={this._onChange}
                loadOptions={(inputValue) =>
                  this.debouncedLoadVideos(inputValue)
                }
              />
            </PrestoNonceProvider>
          </li>
          <li>
            <label>{__("Video Options", "presto-player")}</label>
            <button type="button" onClick={this._onEditVideoClick}>
              {__("Edit Video", "presto-player")}
            </button>
          </li>
          <li>
            <label>{__("New Video", "presto-player")}</label>
            <button type="button" onClick={this._onCreateVideoClick}>
              {__("Create Video", "presto-player")}
            </button>
          </li>
        </ul>
      </div>
    );
  }
}

export default VideoSelector;
