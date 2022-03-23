import { registerBlockType } from "@wordpress/blocks";
import { store, useBlockProps } from "@wordpress/block-editor";
import {
	SelectControl,
	__experimentalNumberControl as NumberControl,
	TextControl,
	Spinner,
} from "@wordpress/components";
import ServerSideRender from "@wordpress/server-side-render";
import metadata from "./block.json";

/**
 * Styles
 */
import "./style.scss";
import "./editor.scss";

registerBlockType(metadata, {
	edit: ({ attributes, setAttributes, isSelected }) => {
		const blockProps = useBlockProps();

		const { orderby, lookback, count, imageSize, text } = attributes;

		const imageSizes = wp.data.select(store).getSettings().imageSizes;

		/**
		 * Transforms an array of post objects to be used in the SelectControl component.
		 *
		 * @param {Array} posts An array of post objects.
		 */
		function selectImageSizes() {
			const options = [];

			imageSizes.forEach((item) => {
				const { slug, name } = item;
				options.push({
					value: slug,
					label: name,
				});
			});

			return options;
		}

		const placeholder = () => {
			return (
				<div className="rhd-related-posts-container">
					<h4 className="rhd-related-posts-title">{text}</h4>
					<div className="rhd-related-posts-placeholder">
						<p>Related Posts</p>
					</div>
				</div>
			);
		};

		return (
			<div {...blockProps}>
				{isSelected ? (
					<>
						<div className="rhd-related-posts-control">
							<SelectControl
								label="Order by"
								value={orderby}
								options={[
									{
										label: "Random",
										value: "rand",
									},
									{
										label: "Post Title",
										value: "title",
									},
									{
										label: "Post Date",
										value: "date",
									},
									{
										label: "ID",
										value: "Post ID",
									},
									{
										label: "None",
										value: "none",
									},
								]}
								onChange={(newValue) => setAttributes({ orderby: newValue })}
							/>
							<NumberControl
								label="Lookback period in days (default: 0)"
								value={lookback}
								onChange={(newValue) =>
									setAttributes({ lookback: parseInt(newValue) })
								}
								min={0}
							/>
							<NumberControl
								label="Post count"
								value={count}
								onChange={(newValue) =>
									setAttributes({ count: parseInt(newValue) })
								}
								min={1}
							/>
							<TextControl
								label="Heading text"
								value={text}
								onChange={(newValue) => setAttributes({ text: newValue })}
							/>
							<SelectControl
								label="Image size"
								value={imageSize}
								options={selectImageSizes()}
								onChange={(newValue) => setAttributes({ imageSize: newValue })}
							/>
						</div>
						<hr />
					</>
				) : null}

				<div className="preview">
					<ServerSideRender
						block="rhd/related-posts"
						attributes={attributes}
						EmptyResponsePlaceholder={placeholder}
					/>
				</div>
			</div>
		);
	},

	save: () => {
		return null;
	},
});
