describe('HelloWorld Component', () => {
    test('renders correctly', () => {
        const component = shallow(<HelloWorld />);
        expect(component).toMatchSnapshot();
    });

    test('displays the correct message', () => {
        const component = shallow(<HelloWorld message="Hello, World!" />);
        expect(component.text()).toBe('Hello, World!');
    });

    test('handles click events', () => {
        const mockOnClick = jest.fn();
        const component = shallow(<HelloWorld onClick={mockOnClick} />);
        component.find('button').simulate('click');
        expect(mockOnClick).toHaveBeenCalled();
    });
});