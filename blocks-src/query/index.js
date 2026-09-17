import { registerBlockVariation } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Fragment } from '@wordpress/element';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

const QUERY_NAMESPACE = 'orbis-tasks/query';
const TASK_STATUS_ALL = 'all';

registerBlockVariation( 'core/query', {
	name: QUERY_NAMESPACE,
	title: __( 'Open tasks', 'orbis-tasks' ),
	description: __( 'Display open tasks.', 'orbis-tasks' ),
	icon: 'list-view',
	attributes: {
		namespace: QUERY_NAMESPACE,
		query: {
			postType: 'orbis_task',
			inherit: false,
			orbisTaskStatus: 'open',
		},
	},
	scope: [ 'inserter' ],
	isActive: ( blockAttributes, variationAttributes ) =>
		blockAttributes.namespace === variationAttributes.namespace &&
		blockAttributes.query?.orbisTaskStatus === 'open',
} );

const withTaskStatusControl = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		if ( 'core/query' !== props.name ) {
			return <BlockEdit { ...props } />;
		}

		const { query } = props.attributes;

		if ( 'orbis_task' !== query?.postType ) {
			return <BlockEdit { ...props } />;
		}

		const status = query.orbisTaskStatus || TASK_STATUS_ALL;
		const setStatus = ( value ) => {
			const nextQuery = { ...query };

			if ( TASK_STATUS_ALL === value ) {
				delete nextQuery.orbisTaskStatus;
			} else {
				nextQuery.orbisTaskStatus = value;
			}

			props.setAttributes( { query: nextQuery } );
		};

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody title={ __( 'Task status', 'orbis-tasks' ) }>
						<SelectControl
							label={ __( 'Status', 'orbis-tasks' ) }
							value={ status }
							options={ [
								{
									label: __( 'All', 'orbis-tasks' ),
									value: TASK_STATUS_ALL,
								},
								{
									label: __( 'Open', 'orbis-tasks' ),
									value: 'open',
								},
								{
									label: __( 'Completed', 'orbis-tasks' ),
									value: 'completed',
								},
							] }
							onChange={ setStatus }
						/>
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	}
);

addFilter(
	'editor.BlockEdit',
	'orbis-tasks/task-status-control',
	withTaskStatusControl
);
