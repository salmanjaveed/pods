import React from 'react';
import classnames from 'classnames';
import PropTypes from 'prop-types';

import ValidationMessages from 'dfv/src/components/validation-messages';
import { requiredValidator } from 'dfv/src/helpers/validators';
import { toBool } from 'dfv/src/helpers/booleans';
import useValidation from 'dfv/src/hooks/useValidation';

import { FIELD_PROP_TYPE_SHAPE } from 'dfv/src/config/prop-types';

import './field-container.scss';

const FieldContainer = ( props ) => {
	const {
		fieldComponent: Field,
		fieldConfig,
		htmlAttr = {},
		value,
		setValue,
	} = props;

	// The only one set up by default here
	// is to validate a required field, but the field child component
	// may set additional rules.
	const [ validationMessages, addValidationRules ] = useValidation(
		[
			{
				rule: requiredValidator( fieldConfig.label ),
				condition: () => true === toBool( fieldConfig.required ),
			},
		],
		value
	);

	return (
		<div
			className={
				classnames(
					'pods-dfv-container',
					`pods-dfv-container-${ fieldConfig?.type }`
				)
			}
		>
			<Field
				value={ value }
				setValue={ setValue }
				isValid={ !! validationMessages.length }
				addValidationRules={ addValidationRules }
				htmlAttr={ htmlAttr }
				{ ...props }
			/>

			{ !! validationMessages.length && (
				<ValidationMessages
					messages={ validationMessages }
				/>
			) }
		</div>
	);
};

FieldContainer.defaultProps = {
	fieldItemData: [],
};

FieldContainer.propTypes = {
	fieldComponent: PropTypes.func.isRequired,
	fieldConfig: FIELD_PROP_TYPE_SHAPE,
	value: PropTypes.any,
	setValue: PropTypes.func.isRequired,
};

export default FieldContainer;